<?php

namespace Passle\PassleSync\Actions;

use Passle\PassleSync\Utils\Utils;
use WP_Error;

class UpdateFeaturedPostAction
{
  public static function execute(string $post_shortcode, bool $is_featured_on_passle_page, bool $is_featured_on_post_page)
  {
    $posts = get_posts([
      "post_type" => PASSLESYNC_POST_TYPE,
      "numberposts" => 1,
      "meta_query" => [
        [
          "key" => "post_shortcode",
          "value" => $post_shortcode,
        ],
      ],
    ]);

    if (empty($posts)) return new WP_Error("404", "No post exists with the shortcode specified");
    $post = $posts[0];

    Utils::clear_featured_posts();

    if ($is_featured_on_passle_page) {
      update_post_meta($post->ID, "post_is_featured_on_passle_page", true);
    }

    if ($is_featured_on_post_page) {
      update_post_meta($post->ID, "post_is_featured_on_post_page", true);
    }
  }
}
