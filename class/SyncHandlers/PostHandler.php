<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\OptionsService;

class PostHandler extends SyncHandlerBase
{
  const RESOURCE = PostResource::class;

  protected static function pre_sync_all_hook()
  {
    Utils::clear_featured_posts();
  }

  protected static function map_data(array $data, int $entity_id)
  {
    $options = OptionsService::get();

    $categories = array();
    if($options->include_categories_from_passle_tag_groups) {
      $categories = static::map_tag_groups_to_categories($data["TagGroups"]);
    }

    // Explicitly setting a post to the default category is not required but it is considered a good practice
    if (empty($categories)) {
      $default_category = get_option('default_category');
      if ($default_category != 0) {
        $categories = array($default_category);
      }
    }
    
    $tag_groups = array();
    //TODO: add options setting check here
    if(true) {
        $tag_groups = static::map_tag_groups_to_custom_taxonomy($data["TagGroups"]);
    }

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
      "tags_input" => $data["Tags"],
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
        "post_tweets" => static::map_tweets($data["Tweets"]),
        "post_total_shares" => $data["TotalShares"],
        "post_total_likes" => $data["TotalLikes"],
        "post_is_repost" => $data["IsRepost"],
        "post_estimated_read_time" => $data["EstimatedReadTimeInSeconds"],
        "post_tags" => $data["Tags"],
        "post_categories" => $categories,
        "post_tag_groups" => $tag_groups,
        "post_image_url" => $data["ImageUrl"],
        "post_featured_item_html" => $data["FeaturedItemHtml"],
        "post_featured_item_position" => $data["FeaturedItemPosition"],
        "post_featured_item_media_type" => $data["FeaturedItemMediaType"],
        "post_featured_item_embed_type" => $data["FeaturedItemEmbedType"],
        "post_featured_item_embed_provider" => $data["FeaturedItemEmbedProvider"],
        "post_opens_in_new_tab" => $data["OpensInNewTab"],
        "post_quote_text" => $data["QuoteText"],
        "post_quote_url" => $data["QuoteUrl"],
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

  public static function map_tweets(array $tweets)
  {
    return array_map(fn ($tweet) => [
      "embed_code" => $tweet["EmbedCode"],
      "tweet_id" => $tweet["TweetId"],
      "screen_name" => $tweet["ScreenName"],
    ], $tweets);
  }

  private static function map_tag_groups_to_categories(array $tag_groups)
  {
      $category_ids = array();

      foreach ($tag_groups as $tag_group) {

        $category_name = $tag_group["Name"];

        // Check if the category already exists
        $category_exists = term_exists($category_name, "category");

        if (!$category_exists) {
          // Category does not exist, so insert it
          $category = wp_insert_term(
              $category_name,
              "category",        
              array(
                "parent" => 0,
              )
          );

          if (is_wp_error($category)) {
            error_log("Error creating category: " . $category->get_error_message() . PHP_EOL);
          }
        }
        else {
            $category = get_term_by("name", $category_name, "category");
        }

        if($category) {
          $category_ids[] = $category->term_id;
        }
      }

      return $category_ids;
  }

  private static function map_tag_groups_to_custom_taxonomy(array $tag_groups)
  {
      $term_ids = array();

      if (taxonomy_exists("tag_group")) {

        foreach($tag_groups as $tag_group) {

            $term_name = $tag_group["Name"];

            // Check if the term already exists 
            $term_exists = term_exists($term_name, "tag_group");

            if (!$term_exists) {
                $term = wp_insert_term(
                    $term_name,
                    "tag_group",
                    array(
                      "parent" => 0
                    )
                );
                
                if (is_wp_error($term)) {
                    error_log("Error creating term: " . $term->get_error_message() . PHP_EOL); 
                }
            } 
            else {
                $term = get_term_by("name", $term_name, "tag_group");
            }

            if ($term) {
                $term_ids[] = $term->term_id;
            }
        }
      }

      return $term_ids;
  }
}
