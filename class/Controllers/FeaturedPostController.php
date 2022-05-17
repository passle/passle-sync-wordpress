<?php

namespace Passle\PassleSync\Controllers;

use \WP_REST_Request;
use \WP_Error;
use Passle\PassleSync\Controllers\ControllerBase;
use Passle\PassleSync\Utils\Utils;

class FeaturedPostController extends ControllerBase
{
  protected string $resource_url = "featured-post";

  public function register_routes()
  {
    $this->register_route("/{$this->resource_url}/update", "POST", "update", "validate_passle_webhook_request");
  }

  public function update(WP_REST_Request $request)
  {
    $data = $request->get_json_params();

    $post_shortcode = $data["PostShortcode"];
    if (empty($post_shortcode)) return new WP_Error("400", "Missing required parameter 'PostShortcode'");

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
