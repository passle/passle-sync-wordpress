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

  public static function sync_one(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    $shortcode = static::get_required_parameter($request, "shortcode");
    
    call_user_func([$resource->sync_handler_name, "sync_one"], $shortcode);
  }

  public static function delete_one(WP_REST_Request $request)
  {
    $resource = static::get_resource_instance();

    $shortcode = static::get_required_parameter($request, "shortcode");
    
    call_user_func([$resource->sync_handler_name, "delete_one"], $shortcode);
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
    static::register_route("/{$resource_name_plural}/sync-one", "POST", "sync_one");
    static::register_route("/{$resource_name_plural}/delete-one", "POST", "delete_one");
    static::register_route("/{$resource_name_plural}/refresh-all", "GET", "refresh_all");
  }

  protected static function get_resource_instance()
  {
    return ResourceRegistryService::get_resource_instance(static::RESOURCE);
  }

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
