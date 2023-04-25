<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Utils\ResourceClassBase;
use Passle\PassleSync\Utils\Utils;

abstract class SyncHandlerBase extends ResourceClassBase
{
  protected abstract static function map_data(array $data, int $entity_id);

  public static function sync_all()
  {
    if (method_exists(static::class, "pre_sync_all_hook")) {
      call_user_func([static::class, "pre_sync_all_hook"]);
    }

    $resource = static::get_resource_instance();

    $wp_entities = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"]);

    $api_entities = call_user_func([$resource->passle_content_service_name, "get_cache"]);

    static::compare_items($wp_entities, $api_entities);
  }

  public static function sync_many(array $shortcodes)
  {
    $resource = static::get_resource_instance();

    $api_entities = call_user_func([$resource->passle_content_service_name, "get_cache"]);

    foreach ($shortcodes as $shortcode) {
      $data = Utils::array_first($api_entities, fn ($entity) => $entity[$resource->get_shortcode_name()] === $shortcode);

      if (!empty($data)) {
        static::create_or_update($data);
      }
    }
  }

  public static function sync_one(string $shortcode)
  {
    static::sync_many([$shortcode]);
  }

  public static function delete_all()
  {
    $resource = static::get_resource_instance();

    $wp_entities = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"]);

    foreach ($wp_entities as $entity) {
      static::delete($entity->ID);
    }
  }

  public static function delete_many(array $shortcodes)
  {
    $resource = static::get_resource_instance();

    $wp_entities = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"], $shortcodes);

    foreach ($wp_entities as $entity) {
      static::delete($entity->ID);
    }
  }

  public static function delete_one(string $shortcode)
  {
    static::delete_many([$shortcode]);
  }

  private static function compare_items(array $wp_entities, array $api_entities)
  {
    $resource = static::get_resource_instance();
    $resource_shortcode_name = $resource->get_shortcode_name();
    $meta_shortcode_name = "{$resource->name_singular}_shortcode";

    $passle_shortcodes = Utils::array_select($api_entities, $resource_shortcode_name);
    $existing_shortcodes = array_map(fn ($item) => $item->{$meta_shortcode_name}, $wp_entities);
    $all_shortcodes = array_unique(array_merge($passle_shortcodes, $existing_shortcodes));

    $shortcodes_to_delete = array_filter($existing_shortcodes, fn ($shortcode) => !in_array($shortcode, $passle_shortcodes));
    $shortcodes_to_sync = array_filter($all_shortcodes, fn ($shortcode) => !in_array($shortcode, $shortcodes_to_delete));

    // Delete
    $items_to_delete = array_filter($wp_entities, fn ($item) => in_array($item->{$meta_shortcode_name}, $shortcodes_to_delete));
    foreach ($items_to_delete as $item) {
      static::delete($item->ID);
    }

    // Add/update
    $items_to_sync = array_filter($api_entities, fn ($item) => in_array($item[$resource_shortcode_name], $shortcodes_to_sync));
    foreach ($items_to_sync as $item) {
      static::create_or_update($item);
    }
  }

  protected static function delete(int $id)
  {
    return wp_delete_post($id, true);
  }

  protected static function create_or_update(array $data)
  {
    $resource = static::get_resource_instance();

    $existing_entities = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"], [
      $data[$resource->get_shortcode_name()],
    ]);

    $entity_id = 0;

    if (!empty($existing_entities)) {
      $entity_id = $existing_entities[0]->ID;
    }

    $postarr = static::map_data($data, $entity_id);

    static::insert_post($postarr, true);
  }

  protected static function insert_post(array $postarr, $wp_error = \false, $fire_after_hooks = \true)
  {
    if (empty($postarr["meta_input"])) {
      return wp_insert_post($postarr, $wp_error, $fire_after_hooks);
    }

    // Find the keys that are arrays, take them out of $postarr and store them in a temporary array
    $postarr_arrays = [];

    foreach ($postarr["meta_input"] as $key => $value) {
      if (gettype($value) !== "array") continue;
      $postarr_arrays[$key] = $value;
      unset($postarr["meta_input"][$key]);
    }

    // Insert the post
    $post_id = wp_insert_post($postarr, $wp_error, $fire_after_hooks);

    // Add metadata for all arrays
    foreach ($postarr_arrays as $key => $value) {
      delete_post_meta($post_id, $key);

      foreach ($value as $item) {
        add_post_meta($post_id, $key, $item);
      }
    }

    $postarr["ID"] = $post_id;

    return $postarr;
  }

  protected static function extract_slug_from_url(string $url)
  {
    return basename($url);
  }
}
