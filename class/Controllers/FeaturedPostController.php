<?php

namespace Passle\PassleSync\Controllers;

use \WP_REST_Request;
use \WP_Error;
use Passle\PassleSync\Controllers\ControllerBase;
use Passle\PassleSync\Utils\Utils;

class FeaturedPostController extends ControllerBase
{
  const RESOURCE_NAME = "featured-post";

  public static function init()
  {
    $resource_name = static::RESOURCE_NAME;

    static::register_route("/{$resource_name}/update", "POST", "update", "validate_passle_webhook_request");
  }

  public function update(WP_REST_Request $request)
  {
    $data = $request->get_json_params();

    $post_shortcode = static::get_required_parameter($request, "PostShortcode");

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

    if ($data["IsFeaturedOnPasslePage"]) {
      update_post_meta($post->ID, "post_is_featured_on_passle_page", true);
    }

    if ($data["IsFeaturedOnPostPage"]) {
      update_post_meta($post->ID, "post_is_featured_on_post_page", true);
    }
  }
}
