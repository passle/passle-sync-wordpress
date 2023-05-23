<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Models\PasslePost;
use Passle\PassleSync\Services\Content\Passle\PasslePostsContentService;

class TemplateService
{
  public static function init()
  {
    add_filter("template_include", [static::class, "add_preview_to_hierarchy"]);
  }

  public static function add_preview_to_hierarchy($original_template)
  {
    global $passle_post, $post, $is_passle_preview;

    if (get_query_var("passle_preview", false)) {
      $is_passle_preview = true;
      $shortcode = get_query_var("passle_preview");
      $post = PasslePostsContentService::fetch_preview($shortcode);
      $passle_post = new PasslePost($post);
      return get_query_template("single-passle-post");
    } else if (get_query_var("post_type") == "passle-post") {
      $is_passle_preview = false;
	  $shortcode = get_query_var("name");
	  $post = PasslePostsContentService::fetch_preview($shortcode);
      $passle_post = new PasslePost($post);
    }

    return $original_template;
  }
}
