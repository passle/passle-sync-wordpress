<?php

namespace Passle\PassleSync\ResponseFactories\Resources;

use ActionScheduler_Store;
use Passle\PassleSync\Models\Admin\PaginatedResponse;
use Passle\PassleSync\Services\SchedulerService;
use Passle\PassleSync\Utils\ResourceClassBase;
use WP_REST_Request;

abstract class ResourceResponseFactoryBase extends ResourceClassBase
{
  public static function make(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    $wp_posts = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"]);
    $api_posts = call_user_func([$resource->passle_content_service_name, "get_cache"]);

    $entities = static::get_models($wp_posts, $api_posts);

    if (method_exists(static::class, "filter_entities_before_pagination")) {
      $entities = call_user_func([static::class, "filter_entities_before_pagination"], $entities);
    }

    // Paginate response
    $current_page = $request["currentPage"] ?? 1;
    $items_per_page = $request["itemsPerPage"] ?? 20;

    $response = PaginatedResponse::make($entities, $current_page, $items_per_page, [
      "pending_sync_count" => static::get_pending_sync_count(),
    ]);

    return $response;
  }

  protected static function get_models(array $wp_entities, array $api_entities)
  {
    $admin_model_name = static::get_resource_instance()->admin_model_name;

    $wp_models = array_map(fn ($entity) => call_user_func([$admin_model_name, "fromWordpressEntity"], $entity)->to_array(), $wp_entities);
    $api_models = array_map(fn ($entity) => call_user_func([$admin_model_name, "fromApiEntity"], $entity)->to_array(), $api_entities);

    // Merge arrays
    $all_models = array_merge($wp_models, $api_models);
    $unique_shortcodes = array_unique(array_column($all_models, "shortcode"));
    $unique_models = array_intersect_key($all_models, $unique_shortcodes);

    return $unique_models;
  }

  protected static function get_pending_sync_count()
  {
    $resource_schedule_group_name = static::get_resource_instance()->get_schedule_group_name();

    $scheduled_actions = SchedulerService::get_scheduled_actions([
      "group" => $resource_schedule_group_name,
      "status" => ActionScheduler_Store::STATUS_PENDING,
      "per_page" => 0,
    ], "ids");

    return count($scheduled_actions);
  }
}
