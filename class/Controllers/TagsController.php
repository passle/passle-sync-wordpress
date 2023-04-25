<?php

namespace Passle\PassleSync\Controllers;

use Exception;
use \WP_REST_Request;
use \WP_POST;
use Passle\PassleSync\Models\PasslePost;
use Passle\PassleSync\Services\Content\Passle\PasslePostsContentService;
use Passle\PassleSync\Services\Content\Passle\PassleTagsContentService;

class TagsController extends ControllerBase
{
  public static function init()
  {
    static::register_route("/tags", "GET", "fetch_tags_data");
  }

  public static function fetch_tags_data()
  {
    [$wordpress_posts, $synced_passle_posts, $unsynced_passle_posts] = static::get_posts();
    [$wordpress_post_tags, $synced_passle_post_tags, $unsynced_passle_post_tags] = static::get_post_tags($wordpress_posts, $synced_passle_posts, $unsynced_passle_posts);

    $api_tags = PassleTagsContentService::fetch_all();
    $all_tags = array_values(array_unique(array_merge($wordpress_post_tags, $synced_passle_post_tags, $api_tags)));

    $tag_counts_in_wordpress_native_posts = array_count_values($wordpress_post_tags);
    $tag_counts_in_wordpress_passle_posts = array_count_values($synced_passle_post_tags);
    $tag_counts_in_unsynced_passle_posts = array_count_values($unsynced_passle_post_tags);

    return array_map(fn ($t) => array(
      "name" => $t,
      "nonPassleCount" => $tag_counts_in_wordpress_native_posts[$t] ?? 0,
      "syncedPassleCount" => $tag_counts_in_wordpress_passle_posts[$t] ?? 0,
      "unsyncedPassleCount" => $tag_counts_in_unsynced_passle_posts[$t] ?? 0,
    ), $all_tags);
  }

  private static function get_posts()
  {
    $wordpress_posts = get_posts([
      "post_type" => "post",
      "numberposts" => -1
    ]);

    $synced_passle_posts = get_posts([
      "post_type" => PASSLESYNC_POST_TYPE,
      "numberposts" => -1
    ]);

    $synced_passle_posts = array_map(fn ($p) => new PasslePost($p), $synced_passle_posts);

    $api_posts = PasslePostsContentService::get_cache();

    $synced_passle_post_shortcodes = array_map(fn ($p) => $p->shortcode, $synced_passle_posts);
    $unsynced_passle_posts = array_filter($api_posts, fn ($post) => !in_array($post["PostShortcode"], $synced_passle_post_shortcodes));

    return [
      $wordpress_posts,
      $synced_passle_posts,
      $unsynced_passle_posts,
    ];
  }

  private static function get_post_tags(array $wordpress_posts, array $synced_passle_posts, array $unsynced_passle_posts)
  {
    $wordpress_post_tags = array_merge(...array_map(fn (WP_POST $post) => array_map(fn ($t) => $t->name, wp_get_post_tags($post->ID)), $wordpress_posts));
    $synced_passle_post_tags = array_merge(...array_map(fn (PasslePost $post) => array_map(fn ($t) => $t->name, $post->tags), $synced_passle_posts));
    $unsynced_passle_post_tags = array_merge(...array_map(fn ($post) => $post["Tags"], $unsynced_passle_posts));

    return [
      $wordpress_post_tags,
      $synced_passle_post_tags,
      $unsynced_passle_post_tags,
    ];
  }
}
