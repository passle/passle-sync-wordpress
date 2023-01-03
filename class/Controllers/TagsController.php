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

  public static function fetch_tags_data(WP_REST_Request $request)
  {
    $wordpress_posts = get_posts([
      "post_type" => "post",
      "numberposts" => -1
    ]);

    $wordpress_native_posts = array_filter($wordpress_posts, fn ($p) => $p->post_type == "post");
    $wordpress_passle_posts = array_map(fn ($p) => new PasslePost($p), array_filter($wordpress_posts, fn ($p) => $p->post_type == PASSLESYNC_POST_TYPE));

    $wordpress_native_post_tags = array_merge(...array_map(fn (WP_POST $post) => array_map(fn ($t) => $t->name, wp_get_post_tags($post->ID)), $wordpress_native_posts));
    $wordpress_passle_post_tags = array_merge(...array_map(fn (PasslePost $post) => array_map(fn ($t) => $t->name, $post->tags), $wordpress_passle_posts));
    $wordpress_passle_post_shortcodes = array_map(fn ($post) => $post->shortcode, $wordpress_passle_posts);

    $passle_api_posts = PasslePostsContentService::fetch_all();
    $passle_api_tags = PassleTagsContentService::fetch_all();

    $unsynced_passle_posts = array_filter($passle_api_posts, fn ($post) => !in_array($post['PostShortcode'], $wordpress_passle_post_shortcodes));
    $unsynced_passle_post_tags = array_merge(...array_map(fn ($post) => $post['Tags'], $unsynced_passle_posts));

    $all_tags = array_values(array_unique(array_merge($wordpress_native_post_tags, $wordpress_passle_post_tags, $passle_api_tags)));

    $tag_counts_in_wordpress_native_posts = array_count_values($wordpress_native_post_tags);
    $tag_counts_in_wordpress_passle_posts = array_count_values($wordpress_passle_post_tags);
    $tag_counts_in_unsynced_passle_posts = array_count_values($unsynced_passle_post_tags);

    return array_map(fn ($t) => array(
      "name" => $t,
      "nonPassleCount" => $tag_counts_in_wordpress_native_posts[$t] ?? 0,
      "syncedPassleCount" => $tag_counts_in_wordpress_passle_posts[$t] ?? 0,
      "unsyncedPassleCount" => $tag_counts_in_unsynced_passle_posts[$t] ?? 0,
    ), $all_tags);
  }
}
