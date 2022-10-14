<?php

namespace Passle\PassleSync\Controllers\Resources;

use Passle\PassleSync\Actions\QueueJobAction;
use Passle\PassleSync\Actions\RefreshAllAction;
use Passle\PassleSync\Controllers\ControllerBase;
use Passle\PassleSync\Services\ResourceRegistryService;
use WP_REST_Request;

abstract class ResourceControllerBase extends ControllerBase
{
  const RESOURCE = "";

  public static function refresh_all()
  {
    $resource = static::get_resource_instance();

    RefreshAllAction::execute($resource);
  }

  public static function get_all(WP_REST_Request $request)
  {
    $response_factory_name = static::get_resource_instance()->response_factory_name;

    $response = call_user_func([$response_factory_name, "make"], $request);

    return $response;
  }

  public static function sync_all(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    QueueJobAction::execute("passle_{$resource->name_plural}_sync_all", [], $resource->get_schedule_group_name());
  }

  public static function delete_all(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    QueueJobAction::execute("passle_{$resource->name_plural}_delete_all", [], $resource->get_schedule_group_name());
  }

  public static function sync_many(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    $entities = static::get_entities_for_request($request);

    $entities = static::filter_entities_before_sync($entities);

    $shortcodes = static::map_entities_to_shortcodes($entities);

    QueueJobAction::execute("passle_{$resource->name_plural}_sync_many", [$shortcodes], $resource->get_schedule_group_name());
  }

  public static function delete_many(WP_REST_Request $request)
  {
    $entities = static::get_entities_for_request($request);

    $shortcodes = static::map_entities_to_shortcodes($entities);

    $resource = static::get_resource_instance();

    QueueJobAction::execute("passle_{$resource->name_plural}_delete_many", [$shortcodes], $resource->get_schedule_group_name());
  }

  public static function update(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    // Getting the entities here allows for filtering, but it also updates the cache for when the sync job runs
    $entities = static::get_entities_for_request($request, $resource->get_shortcode_name(), "fetch_by_shortcode");

    $entities = static::filter_entities_before_sync($entities);

    $shortcodes = static::map_entities_to_shortcodes($entities);

    call_user_func([$resource->sync_handler_name, "sync_many"], $shortcodes);
  }

  public static function delete(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    $entities = static::get_entities_for_request($request);

    call_user_func([$resource->sync_handler_name, "delete_many"], $entities);
  }

  public static function init()
  {
    $resource_name_plural = static::get_resource_instance()->name_plural;

    // Admin dashboard routes
    static::register_route("/{$resource_name_plural}", "GET", "get_all");
    static::register_route("/{$resource_name_plural}/sync-all", "POST", "sync_all");
    static::register_route("/{$resource_name_plural}/delete-all", "POST", "delete_all");
    static::register_route("/{$resource_name_plural}/sync-many", "POST", "sync_many");
    static::register_route("/{$resource_name_plural}/delete-many", "POST", "delete_many");
    static::register_route("/{$resource_name_plural}/refresh-all", "GET", "refresh_all");

    // Webhooks
    static::register_route("/{$resource_name_plural}/update", "POST", "update", "validate_passle_webhook_request");
    static::register_route("/{$resource_name_plural}/delete", "POST", "delete", "validate_passle_webhook_request");
  }

  protected static function get_resource_instance()
  {
    return ResourceRegistryService::get_resource_instance(static::RESOURCE);
  }

  /**
   * Filter out posts and authors that do not belong to the list of Passle shortcodes we want to sync content from
   */
  protected static abstract function filter_entities_before_sync(array $entities);

  private static function get_entities_for_request(WP_REST_Request $request, string $shortcode_name = "shortcodes", string $function_to_call = "fetch_multiple_by_shortcode")
  {
    $resource_passle_content_service = static::get_resource_instance()->passle_content_service_name;

    $shortcodes = static::get_required_parameter($request, $shortcode_name);

    $entities = call_user_func([$resource_passle_content_service, $function_to_call], $shortcodes);

    return $entities;
  }

  private static function map_entities_to_shortcodes(array $entities)
  {
    $resource_shortcode_name = static::get_resource_instance()->get_shortcode_name();

    $entities = array_map(fn ($entity) => $entity[$resource_shortcode_name], $entities);

    return $entities;
  }
}
