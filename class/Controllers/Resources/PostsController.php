<?php

namespace Passle\PassleSync\Controllers\Resources;

use Exception;
use Passle\PassleSync\Controllers\Resources\ResourceControllerBase;
use Passle\PassleSync\Models\PaginatedResponse;
use Passle\PassleSync\Models\Post;
use Passle\PassleSync\SyncHandlers\Handlers\PostHandler;
use Passle\PassleSync\Services\Content\PostsWordpressContentService;
use Passle\PassleSync\Services\PassleContentService;
use Passle\PassleSync\SyncHandlers\SyncHandlerBase;

class PostsController extends ResourceControllerBase
{
  protected PostsWordpressContentService $wordpress_content_service;
  protected PassleContentService $passle_content_service;

  private SyncHandlerBase $sync_handler;

  public function __construct(
    PostsWordpressContentService $wordpress_content_service,
    PassleContentService $passle_content_service,
    PostHandler $sync_handler
  ) {
    parent::__construct("posts");
    $this->sync_handler = $sync_handler;
    $this->passle_content_service = $passle_content_service;
    $this->wordpress_content_service = $wordpress_content_service;
  }

  public function refresh_all()
  {
    $this->passle_content_service->update_all_passle_posts_from_api();
  }

  public function get_all($request)
  {
    $wp_posts = $this->wordpress_content_service->get_items();
    $api_posts = $this->passle_content_service->get_stored_passle_posts_from_api();

    // Create Post objects from models
    $wp_post_models = array_map(fn ($post) => Post::fromWordpressPost($post)->to_array(), $wp_posts);
    $api_post_models = array_map(fn ($post) => Post::fromPasslePost($post)->to_array(), $api_posts);

    // Merge arrays
    $all_models = array_merge($wp_post_models, $api_post_models);
    $unique_shortcodes = array_unique(array_column($all_models, "shortcode"));
    $unique_models = array_intersect_key($all_models, $unique_shortcodes);

    // Sort unique models by publishedDate descending, then format publishedDate
    usort($unique_models, fn ($a, $b) => $b["publishedDate"] <=> $a["publishedDate"]);
    array_walk($unique_models, fn (&$key) => $key["publishedDate"] = date("d/m/Y H:i", $key["publishedDate"]));

    // Paginate response
    $current_page = $request["currentPage"] ?? 1;
    $items_per_page = $request["itemsPerPage"] ?? 20;

    return PaginatedResponse::make($unique_models, $current_page, $items_per_page);
  }

  public function sync_all($request)
  {
    $this->sync_handler->sync_all();
  }

  public function delete_all($request)
  {
    $this->sync_handler->delete_all();
  }

  public function sync_many($request)
  {
    $data = $request->get_json_params();

    $shortcodes = $data["shortcodes"] ?? null;

    if ($shortcodes == null) {
      throw new Exception("Missing shortcodes parameter", 400);
    }

    $posts = $this->passle_content_service->get_posts($shortcodes);

    // Filter out posts that aren't in the list of Passle shortcodes we want to sync content from
    // This is useful to prevent reposts being added when a post is saved
    $passle_shortcodes = get_option(PASSLESYNC_SHORTCODE);
    $posts = array_filter($posts, fn ($post) => in_array($post["PassleShortcode"], $passle_shortcodes));

    $this->sync_handler->sync_many($posts);
  }

  public function delete_many($request)
  {
    $data = $request->get_json_params();

    $shortcodes = $data["shortcodes"] ?? null;

    if ($shortcodes == null) {
      throw new Exception("Missing shortcodes parameter", 400);
    }

    $posts = $this->passle_content_service->get_posts($shortcodes);

    $this->sync_handler->delete_many($posts);
  }
}
