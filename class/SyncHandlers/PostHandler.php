<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\AuthorHandler;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\Content\Passle\PassleTagGroupsContentService;

class PostHandler extends SyncHandlerBase
{
  const RESOURCE = PostResource::class;

  /** Option-name prefix for the per-Passle-shortcode page accumulator - see post_page_sync_hook(). */
  const PAGE_ACCUMULATOR_OPTION_PREFIX = "passle_sync_posts_page_accumulator_";

  /** Clears the featured-posts cache before a run, since a full sync can change which posts are featured. */
  protected static function pre_sync_all_hook()
  {
    Utils::clear_featured_posts();
  }

  /** Fires an action other code can hook into once every post has been synced. */
  protected static function post_sync_all_hook()
  {
    do_action("passle_post_sync_all_complete");
  }

  /** Clears any stale legacy pending-deletion flag on this post and fires a per-entity completion action. */
  protected static function post_sync_one_hook(int $entity_id)
  {
    delete_post_meta($entity_id, '_pending_deletion');
    do_action("passle_post_sync_one_complete", $entity_id);
  }

  /** Reads the stored page-resume option for posts (defaults to page 1). */
  protected static function get_last_synced_page()
  {
    $resource = static::get_resource_instance();
    $last_synced_page = get_option($resource->last_synced_page_option_name);
    return $last_synced_page !== false ? $last_synced_page : 1;
  }

  /** Persists the page-resume option for posts. */
  protected static function set_last_synced_page(int $page_number)
  {
    $resource = static::get_resource_instance();
    update_option($resource->last_synced_page_option_name, $page_number);
  }

  /**
   * Accumulates this run's shortcodes and earliest PublishedDate across pages (keyed per
   * Passle shortcode via get_page_accumulator_option_name(), so concurrently-syncing Passle
   * accounts can't corrupt each other's state), and once every page of the run has completed,
   * hands the accumulated result to reconcile_deleted_posts() and clears the stored state.
   *
   * The Passle posts API only ever returns posts published after an account-level start
   * date, so a post missing from a run's results is only a deletion candidate if it falls
   * within the date range that run actually covered - see reconcile_deleted_posts().
   */
  protected static function post_page_sync_hook(string $url, array $api_entities, int $page_number, int $total_pages)
  {
    $resource = static::get_resource_instance();
    $shortcode_name = $resource->get_shortcode_name();
    $option_name = static::get_page_accumulator_option_name($url);

    $accumulator = $page_number === 1
      ? ["shortcodes" => [], "min_published_date" => null, "pages_seen" => []]
      : get_option($option_name, ["shortcodes" => [], "min_published_date" => null, "pages_seen" => []]);

    foreach ($api_entities as $item) {
      $accumulator["shortcodes"][] = $item[$shortcode_name];

      $published_date = $item["PublishedDate"] ?? null;
      if (!empty($published_date) && ($accumulator["min_published_date"] === null || $published_date < $accumulator["min_published_date"])) {
        $accumulator["min_published_date"] = $published_date;
      }
    }

    // This hook only runs once a page has fully synced without throwing (SyncHandlerBase::sync_page()
    // throws before reaching here on a bad/failed API response, and create_or_update() throwing on any
    // item aborts the rest of the page). So a page that fails to sync is never recorded here, and
    // reconciliation below - gated on every page number being present - never runs for that attempt.
    $accumulator["pages_seen"][$page_number] = true;

    if (count($accumulator["pages_seen"]) >= $total_pages) {
      static::reconcile_deleted_posts($accumulator);
      delete_option($option_name);
    } else {
      update_option($option_name, $accumulator, false);
    }
  }

  /** Derives the per-Passle-shortcode accumulator option name from a page's request URL. */
  private static function get_page_accumulator_option_name(string $url)
  {
    $query = parse_url($url, PHP_URL_QUERY) ?? "";
    parse_str($query, $params);
    $passle_shortcode = $params["PassleShortcode"] ?? "default";

    return self::PAGE_ACCUMULATOR_OPTION_PREFIX . $passle_shortcode;
  }

  /**
   * Trashes (not force-deletes - recoverable if this is ever wrong) WP posts that fall
   * within this run's observed date window (post_date >= the earliest PublishedDate the
   * API returned this run) but whose shortcode wasn't in the accumulated API results.
   * Posts older than that floor are never touched here - the API's start-date cutoff means
   * their absence proves nothing, so they're left to the DELETE_POST webhook only.
   */
  private static function reconcile_deleted_posts(array $accumulator)
  {
    // Without a floor we have no safe window to compare against - do nothing.
    if (empty($accumulator["min_published_date"])) {
      return;
    }

    $resource = static::get_resource_instance();
    $meta_shortcode_name = $resource->get_meta_shortcode_name();
    $synced_shortcodes = $accumulator["shortcodes"];

    $wp_entities = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"]);

    $in_window_entities = array_filter(
      $wp_entities,
      fn ($entity) => $entity->post_date >= $accumulator["min_published_date"]
    );

    $entities_to_delete = array_filter($in_window_entities, function ($entity) use ($meta_shortcode_name, $synced_shortcodes) {
      $shortcode = is_array($entity->{$meta_shortcode_name}) ? $entity->{$meta_shortcode_name}[0] : $entity->{$meta_shortcode_name};
      return !in_array($shortcode, $synced_shortcodes);
    });

    if (empty($entities_to_delete)) {
      return;
    }

    write_log(sprintf(
      "Passle sync: trashing %d of %d in-window posts (published on or after %s) that were absent from the API response.",
      count($entities_to_delete),
      count($in_window_entities),
      $accumulator["min_published_date"]
    ));

    foreach ($entities_to_delete as $entity) {
      wp_trash_post($entity->ID);
    }
  }

  /** Maps a Passle post record to a WP postarr for the post CPT, including tags/tag-groups and all post_* meta fields. */
  protected static function map_data(array $data, int $entity_id)
  {
    $tag_mappings = $data["TagMappings"];
    $tag_to_aliases_map = PassleTagGroupsContentService::create_tag_to_aliases_map($tag_mappings); 
    
    $tag_groups = $data["TagGroups"];
    $mapped_tag_groups = static::map_tag_groups($tag_groups);
    $tag_groups_with_aliases = PassleTagGroupsContentService::create_tag_groups_with_tag_aliases($mapped_tag_groups, $tag_mappings);

    $post_tags = static::map_tags($tag_to_aliases_map);

    $postarr = [
      "ID" => $entity_id,
      "post_title" => $data["PostTitle"],
      "post_name" => $data["PostShortcode"],
      "post_date" => $data["PublishedDate"],
      "post_type" => PASSLESYNC_POST_TYPE,
      "post_content" => $data["PostContentHtml"],
      "post_excerpt" => $data["ContentTextSnippet"],
      "post_status" => "publish",
      "comment_status" => "closed",
      "tags_input" => $post_tags,
      "meta_input" => [
        "post_shortcode" => $data["PostShortcode"],
        "passle_shortcode" => $data["PassleShortcode"],
        "post_url" => $data["PostUrl"],
        "post_slug" => static::extract_slug_from_url($data["PostUrl"]),
        "post_authors" => static::map_authors($data["Authors"]),
        "post_author_shortcodes" => static::map_author_shortcodes($data["Authors"]),
        "post_coauthors" => static::map_authors($data["CoAuthors"]),
        "post_coauthors_shortcodes" => static::map_author_shortcodes($data["CoAuthors"]),
        "post_share_views" => static::map_share_views($data["ShareViews"]),
        "post_total_shares" => $data["TotalShares"],
        "post_total_likes" => $data["TotalLikes"],
        "post_is_repost" => $data["IsRepost"],
        "post_estimated_read_time" => $data["EstimatedReadTimeInSeconds"],
        "post_tags" => $post_tags,
        "post_tag_to_aliases_map" => $tag_to_aliases_map,
        "post_tag_group_tags" => $post_tags,
        "post_tag_groups" => $tag_groups_with_aliases,
        "post_image_url" => $data["ImageUrl"],
        "post_featured_item_html" => $data["FeaturedItemHtml"],
        "post_featured_item_position" => $data["FeaturedItemPosition"],
        "post_featured_item_media_type" => $data["FeaturedItemMediaType"],
        "post_featured_item_embed_type" => $data["FeaturedItemEmbedType"],
        "post_featured_item_embed_provider" => $data["FeaturedItemEmbedProvider"],
        "post_opens_in_new_tab" => $data["OpensInNewTab"],
        "post_quote_text" => $data["QuoteText"],
        "post_quote_url" => $data["QuoteUrl"],
        "post_metadata_title" => $data["MetaData"]['Title'] ?? "",
        "post_metadata_description" => $data["MetaData"]['Description'] ?? "",
      ],
    ];

    if ($data["IsFeaturedOnPasslePage"]) {
      $postarr["meta_input"]["post_is_featured_on_passle_page"] = true;
    }

    if ($data["IsFeaturedOnPostPage"]) {
      $postarr["meta_input"]["post_is_featured_on_post_page"] = true;
    }

    return $postarr;
  }

  /** Maps a post's authors to the stored response format, and kicks off an author sync for any Passle author WP doesn't have a post for yet. */
  public static function map_authors(array $authors)
  {
    $wp_authors = get_posts([
      "post_type" => PASSLESYNC_AUTHOR_TYPE,
      "numberposts" => -1
    ]);

    $authors_to_sync = array();
    $author_response_models = array_map(function ($author) use ($wp_authors, &$authors_to_sync) { 
      
      // trigger author syncronization if there is no Passle author with the given shortcode in WP
      $author_from_wp = Utils::array_first($wp_authors, fn ($wp_author) => $wp_author->post_name === $author["Shortcode"]);
      if (empty($author_from_wp) && !empty($author["Shortcode"])) {
        array_push($authors_to_sync, $author["Shortcode"]);
      }
      
      return [
        "shortcode" => $author["Shortcode"],
        "name" => $author["Name"],
        "image_url" => $author["ImageUrl"],
        "profile_url" => $author["ProfileUrl"],
        "role" => $author["Role"],
        "twitter_screen_name" => $author["TwitterScreenName"]
      ];
    }, $authors); 

    if (!empty($authors_to_sync)) {
      AuthorHandler::sync_many(array_unique($authors_to_sync));
    }
    
    return $author_response_models;
  }

  /** Extracts just the shortcodes from a post's authors/co-authors array. */
  public static function map_author_shortcodes(array $authors)
  {
    return array_map(fn ($author) => $author["Shortcode"], $authors);
  }

  /** Maps per-social-network share stats to the stored `post_share_views` meta format. */
  public static function map_share_views(array $share_views)
  {
    return array_map(fn ($share_view) => [
      "social_network" => $share_view["SocialNetwork"],
      "total_views" => $share_view["TotalViews"],
    ], $share_views);
  }

  /**
  * Formats tag groups from the format in which they appear on a post response
  * to the format in which they appear in taggroups api response
  *
  * @param array<int array<int, {TagMappings: array<int, {Tag: string, Label: string, Aliases: string[]}>}>>
  * @returns array<int, array{Name: string, Tags: string[]}>
  */
  public static function map_tag_groups(array $post_tag_groups)
  {
    return array_map(function($post_tag_group){
      return [
        "Name" => $post_tag_group["Name"],
        "Tags" => array_map(function($tag_mapping) {
          return $tag_mapping["Tag"];
        }, $post_tag_group["TagMappings"])
      ];
    }, $post_tag_groups);
  }

  /**
  *  Flattens $tag_to_aliases_map to a string array that contains
  *  either a tag if the tag has no aliases or the aliases if a tag has aliases
  *
  * @param array<int, array<string, array{Aliases: string[]}>> $tag_to_aliases_map
  * @return string[] Flattened list of all tags (or aliases of tags if aliases exist)
  *
  * e.g:
  *
  * For input: 
  * [
  *   ["tagA" => ["Aliases" => ["tag1", "tag2"]]],
  *   ["tagB" => ["Aliases" => []]],
  *   ["tagC" => ["Aliases" => ["tag2"]]]
  * ]
  * 
  * The output should be:
  * ["tag1", "tag2", "tagB"]
  */
  public static function map_tags(array $tag_to_aliases_map)
  {
    if (empty($tag_to_aliases_map)) {
      return array();
    }

    return array_unique(
      array_merge(
        ...array_map(
          function($tag_to_aliases_item) { 
            $tag = key($tag_to_aliases_item);
            $aliases = $tag_to_aliases_item[$tag]["Aliases"];
            if (empty($aliases)) { 
              return [$tag]; 
            } 
            return $aliases; 
          }, 
          $tag_to_aliases_map
        )
      )
    );
  }
}
