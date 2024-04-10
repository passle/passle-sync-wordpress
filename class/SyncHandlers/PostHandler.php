<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\OptionsService;
use Passle\PassleSync\Services\TaxonomyRegistryService;

class PostHandler extends SyncHandlerBase
{
  const RESOURCE = PostResource::class;

  protected static function pre_sync_all_hook()
  {
    Utils::clear_featured_posts();
  }

  protected static function map_data(array $data, int $entity_id)
  {
    $tags = static::map_tags_and_aliases($data["TagMappings"]);
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
      "tags_input" => $tags,
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
        "post_tags" => $tags,
        "post_tag_group_tags" => $tags,
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

  public static function map_authors(array $authors)
  {
    return array_map(fn ($author) => [
      "shortcode" => $author["Shortcode"],
      "name" => $author["Name"],
      "image_url" => $author["ImageUrl"],
      "profile_url" => $author["ProfileUrl"],
      "role" => $author["Role"],
      "twitter_screen_name" => $author["TwitterScreenName"],
    ], $authors);
  }

  public static function map_author_shortcodes(array $authors)
  {
    return array_map(fn ($author) => $author["Shortcode"], $authors);
  }

  public static function map_share_views(array $share_views)
  {
    return array_map(fn ($share_view) => [
      "social_network" => $share_view["SocialNetwork"],
      "total_views" => $share_view["TotalViews"],
    ], $share_views);
  }

  public static function map_tags_and_aliases(array $tag_mappings)
  {
    $tags_to_return = array();

    if (empty($tag_mappings)) {
      return $tags_to_return;
    }
    
    $wp_tag_names = Utils::get_HTML_decoded_wp_tag_names();

    foreach ($tag_mappings as $tag_mapping) {
      $tag = $tag_mapping['Tag'];
      $index = array_search($tag, $wp_tag_names);
      
      if ($index !== false) {
        array_push($tags_to_return, $wp_tag_names[$index]);
        continue;
      }

      $alias_array = $tag_mapping['Aliases'];
      $tags_count = count($tags_to_return);

      if ($alias_array !== null) {
        foreach ($alias_array as $alias) {
          $index = array_search($alias, $wp_tag_names);

          if ($index !== false) {
            array_push($tags_to_return, $alias);
            continue;
          }
        }
      }
      if ($tags_count === count($tags_to_return)) {
        array_push($tags_to_return, $tag_mapping["Tag"]);
      }
    }
    return array_unique($tags_to_return);
  }
}
