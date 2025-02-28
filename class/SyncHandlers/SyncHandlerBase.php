<?php

namespace Passle\PassleSync\SyncHandlers;

use Exception;
use Passle\PassleSync\Utils\ResourceClassBase;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Services\OptionsService;

abstract class SyncHandlerBase extends ResourceClassBase
{
  protected abstract static function map_data(array $data, int $entity_id);

  protected abstract static function pre_sync_all_hook();

  protected abstract static function post_sync_all_hook();

  protected abstract static function post_sync_one_hook(int $entity_id);

  public static function sync_all()
  {
    static::pre_sync_all_hook();
 
    $resource = static::get_resource_instance();

    $wp_entities = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"]);

    static::batch_sync_all($wp_entities);

    static::post_sync_all_hook();
  }

  public static function sync_many(array $shortcodes)
  {
    $resource = static::get_resource_instance();

    $cached_api_entities = call_user_func([$resource->passle_content_service_name, "get_cache"]);

    foreach ($shortcodes as $shortcode) {
      $data = Utils::array_first($cached_api_entities, fn ($entity) => $entity[$resource->get_shortcode_name()] === $shortcode);
      
      // attempt to fetch something from the API if it doesn't exist in cache
      if (empty($data)) {
        $api_entities = call_user_func([$resource->passle_content_service_name, "fetch_by_shortcode"], $shortcode);
        $data = Utils::array_first($api_entities, fn ($entity) => $entity[$resource->get_shortcode_name()] === $shortcode);
      } 

      if (!empty($data)) {
        static::create_or_update($data);
      } else {
        error_log('Error fetching data for shortcode: ' . $shortcode);
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
    $existing_shortcodes = array_map(function ($item) use ($meta_shortcode_name) {
      // post_shortcode can be an array. We need to return the string value
      return is_array($item->{$meta_shortcode_name}) ? $item->{$meta_shortcode_name}[0] : $item->{$meta_shortcode_name};
    }, $wp_entities);
    $all_shortcodes = array_unique(array_merge($passle_shortcodes, $existing_shortcodes));

    // Items
    $shortcodes_pending = array_filter($existing_shortcodes, fn ($shortcode) => !in_array($shortcode, $passle_shortcodes));
    $shortcodes_to_sync = array_filter($all_shortcodes, fn ($shortcode) => !in_array($shortcode, $shortcodes_pending));

    // Add/update
    $items_to_sync = array_filter($api_entities, fn ($item) => in_array($item[$resource_shortcode_name], $shortcodes_to_sync));
    foreach ($items_to_sync as $item) {
      static::create_or_update($item);
    }

    return array_filter($wp_entities, function ($item) use ($meta_shortcode_name, $shortcodes_pending) {
      // post_shortcode can be an array. We need to use the string value to check if it is inside $shortcodes_pending
      $shortcode = is_array($item->{$meta_shortcode_name}) ? $item->{$meta_shortcode_name}[0] : $item->{$meta_shortcode_name};
      return in_array($shortcode, $shortcodes_pending);
    });
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
    $options = OptionsService::get();

    if (empty($postarr["meta_input"])) {
      remove_filter('content_save_pre', 'wp_filter_post_kses');
      $post_id = wp_insert_post($postarr, $wp_error, $fire_after_hooks);
      add_filter('content_save_pre', 'wp_filter_post_kses');
      static::post_sync_one_hook($post_id);
      return $post_id;
    }

    // Find the keys that are arrays, take them out of $postarr and store them in a temporary array
    $postarr_arrays = [];

    foreach ($postarr["meta_input"] as $key => $value) {
      if (gettype($value) !== "array") continue;
      $postarr_arrays[$key] = $value;
      unset($postarr["meta_input"][$key]);
    }

    // remove the post sanitizer filter before saving.
    remove_filter('content_save_pre', 'wp_filter_post_kses');
    // Insert the post
    $post_id = wp_insert_post($postarr, $wp_error, $fire_after_hooks);
    // add the filter back after saving.
    add_filter('content_save_pre', 'wp_filter_post_kses');

    // Create post tags with aliases in the default post_tag taxonomy
    if (!empty($postarr_arrays["post_tags_with_aliases"])) {
      foreach ($postarr_arrays["post_tags_with_aliases"] as $tag_data) {
        foreach($tag_data as $tag_name => $tag_info){
          $aliases = isset($tag_info['aliases']) ? $tag_info['aliases'] : array();
          // Check if the tag already exists
          $existing_term = get_term_by('name', $tag_name, 'post_tag');
          if ($existing_term === false) {
            // Skip this tag if its not meant to be added to the post
            if (!in_array($tag_name, $postarr_arrays["post_tags"])) continue;
            // If the tag doesn't exist, create it
            $term = wp_insert_term($tag_name, 'post_tag');
            // Check if the tag was created successfully
            if (!is_wp_error($term)) {
              // Add custom field (aliases) to the tag
              if (!empty($aliases)) {
                update_term_meta($term['term_id'], 'aliases', $aliases);
              }
            } else {
              error_log('Error creating tag: ' . $term->get_error_message());
            }
          } else {
            // If the tag already exists, update its custom field (aliases)
            $term_id = $existing_term->term_id;
            // Update custom field (aliases) for the existing tag
            update_term_meta($term_id, 'aliases', $aliases);
          }
        }
      }
    }
    unset($postarr_arrays["post_tags_with_aliases"]);

    // Set post taxonomy terms based on tags
    if (!empty($postarr_arrays["post_tag_group_tags"]) && $options->include_passle_tag_groups) {
      if ($options->passle_tag_groups_can_use_existing_taxonomy) {
        $taxonomies = get_taxonomies(array("public" => true, "_builtin" => false));
      } else {
        $taxonomies = get_taxonomies(array("object_type" => array(PASSLESYNC_POST_TYPE), "public" => true, "_builtin" => false));
      }
      foreach ($taxonomies as $taxonomy) {
        // Remove all terms for the given taxonomy by setting terms to an empty array
        wp_set_object_terms($post_id, array(), $taxonomy);
        foreach ($postarr_arrays["post_tag_group_tags"] as $tag) {
          $term = get_term_by("name", $tag, $taxonomy);
          if ($term != null && $term->name && $term->taxonomy) {
            wp_set_object_terms($post_id, $term->name, $term->taxonomy, true);
          }
        }
      }
    }
    unset($postarr_arrays["post_tag_group_tags"]);

    // Add metadata for all arrays
    foreach ($postarr_arrays as $key => $value) {
      delete_post_meta($post_id, $key);
      
      foreach ($value as $item) {
        add_post_meta($post_id, $key, $item);
      }
    }

    $postarr["ID"] = $post_id;

    static::post_sync_one_hook($post_id);

    return $postarr;
  }

  protected static function extract_slug_from_url(string $url)
  {
    return basename($url);
  }

  protected static function batch_sync_all(array $wp_entities)
  {
    $passle_shortcodes = OptionsService::get()->passle_shortcodes;

    foreach ($passle_shortcodes as &$passle_shortcode) {
      static::sync_all_by_passle($passle_shortcode, $wp_entities);
    }
  }


  public static function sync_all_by_passle(string $passle_shortcode, array $wp_entities)
  {
    $resource = static::get_resource_instance();

    $url = (new UrlFactory())
      ->path("passlesync/{$resource->name_plural}")
      ->parameters([
        "PassleShortcode" => $passle_shortcode,
        "ItemsPerPage" => "100"
      ])
      ->build();

    static::sync_all_paginated($url, 1, $wp_entities);
  }


  protected static function sync_all_paginated(string $url, int $page_number = 1, array $wp_entities)
  {
    $resource = static::get_resource_instance();
    $max_pages = 1000; // Maximum number of pages to process
    
    while ($page_number <= $max_pages) {

        $next_url = call_user_func([$resource->passle_content_service_name, "get_next_url"], $url, $page_number);
        $response = call_user_func([$resource->passle_content_service_name, "get"], $next_url);

        // Validate the API response
        if (!isset($response[ucfirst($resource->name_plural)])) {
            throw new Exception("Failed to get data from the API", 500);
        }

        $response = $response[ucfirst($resource->name_plural)];
        
        if (empty($response)) {
            break; // No more items
        }

        // Compare and process the items, update pending entities array
        $wp_entities = static::compare_items($wp_entities, $response);

        $page_number += 1;
    }

    // Delete unused entities
    foreach ($wp_entities as $item) {
        static::delete($item->ID);
    }

    return;
  }
}
