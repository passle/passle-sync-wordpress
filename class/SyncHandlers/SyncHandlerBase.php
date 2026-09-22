<?php

namespace Passle\PassleSync\SyncHandlers;

use Exception;
use Passle\PassleSync\Utils\ResourceClassBase;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Services\OptionsService;
use Passle\PassleSync\Actions\QueueJobAction;

abstract class SyncHandlerBase extends ResourceClassBase
{
  /** Maps one raw Passle API record + the WP post ID it corresponds to (0 if new) into a wp_insert_post()-shaped array. */
  protected abstract static function map_data(array $data, int $entity_id);

  /** Runs once, before a sync_all() run starts syncing any Passle shortcode. */
  protected abstract static function pre_sync_all_hook();

  /** Runs once, after a sync_all() run has finished syncing every configured Passle shortcode. */
  protected abstract static function post_sync_all_hook();

  /** Runs after a single entity has been inserted/updated in WP, e.g. to fire a completion action. */
  protected abstract static function post_sync_one_hook(int $entity_id);

  /** Returns the page number a paginated sync should resume from (1 if there's no sync in progress). */
  protected abstract static function get_last_synced_page();

  /** Persists progress through a paginated sync so it can resume if interrupted. */
  protected abstract static function set_last_synced_page(int $page_number);

  /**
   * Runs after every item on one page has been created/updated during a paginated sync.
   * Used by PostHandler to accumulate this run's results for deletion reconciliation once
   * every page has completed; a no-op for resources (like authors) that don't reconcile deletions.
   */
  protected abstract static function post_page_sync_hook(string $url, array $api_entities, int $page_number, int $total_pages);

  /** Entry point for a full sync: runs the pre-hook, syncs every configured Passle shortcode, then the post-hook. */
  public static function sync_all()
  {
    static::pre_sync_all_hook();

    $resource = static::get_resource_instance();

    static::batch_sync_all();

    static::post_sync_all_hook();
  }

  /** Syncs specific shortcodes on demand (e.g. from a SYNC_POST/SYNC_AUTHOR webhook), using the API cache first and falling back to a live API call. */
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
        write_log('Error fetching data for shortcode: ' . $shortcode);
      } 
    }
  }

  /** Convenience wrapper for syncing a single shortcode. */
  public static function sync_one(string $shortcode)
  {
    static::sync_many([$shortcode]);
  }

  /** Deletes every WP entity of this type, fetched in batches. Used for a full reset (e.g. plugin deactivation), not part of routine syncing. */
  public static function delete_all()
  {
    $resource = static::get_resource_instance();

    $paged = 1;
    $per_page = 50;

    do {

        $wp_entities = call_user_func(
            [$resource->wordpress_content_service_name, "fetch_entities"],
            [],
            $paged,
            $per_page
        );

        foreach ($wp_entities as $entity) {
            static::delete($entity->ID);
        }

        $paged++;

    } while (!empty($wp_entities));

    static::set_last_synced_page(1);
  }

  /** Deletes the WP entities matching the given shortcodes. Force-deletes, so only call it for entities confirmed gone at the source (e.g. a DELETE webhook). */
  public static function delete_many(array $shortcodes)
  {
    $resource = static::get_resource_instance();

    $wp_entities = call_user_func([$resource->wordpress_content_service_name, "fetch_entities"], $shortcodes);

    foreach ($wp_entities as $entity) {
      static::delete($entity->ID);
    }
  }

  /** Convenience wrapper for deleting a single shortcode - the DELETE_POST/DELETE_AUTHOR webhook path. */
  public static function delete_one(string $shortcode)
  {
    static::delete_many([$shortcode]);
  }

  /** Force-deletes (bypasses trash) a single WP post by ID. */
  protected static function delete(int $id)
  {
    return wp_delete_post($id, true);
  }

  /** Looks up any existing WP entity for this data's shortcode, then inserts or updates it accordingly. */
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

  /**
   * Wraps wp_insert_post() to handle meta_input values wp_insert_post() can't take directly:
   * array-valued meta (stored via add_post_meta() per item), post_tag terms with alias handling,
   * and tag-group taxonomy terms. Falls back to a plain wp_insert_post() when there's no array meta to handle.
   */
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
    if (!empty($postarr_arrays["post_tag_to_aliases_map"])) {
      foreach ($postarr_arrays["post_tag_to_aliases_map"] as $tag_data) {
        foreach($tag_data as $tag_name => $tag_info){
          $aliases = isset($tag_info['Aliases']) ? $tag_info['Aliases'] : array();
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
              write_log('Error creating tag: ' . $term->get_error_message());
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
    unset($postarr_arrays["post_tag_to_aliases_map"]);

    // Set post taxonomy terms based on tags
    if (!empty($postarr_arrays["post_tag_group_tags"]) && $options->include_passle_tag_groups) {
      $all_taxonomies = get_taxonomies(['public' => true, '_builtin' => false], 'objects');
      $taxonomies = [];

      foreach ($all_taxonomies as $name => $tax) {
        if (in_array(PASSLESYNC_POST_TYPE, $tax->object_type)) {
          $taxonomies[$name] = $tax;
        }
      }

      foreach ($taxonomies as $taxonomy) {
        wp_set_object_terms($post_id, array(), $taxonomy->name); // Clear terms

        foreach ($postarr_arrays["post_tag_group_tags"] as $tag) {
          $term = get_term_by("name", $tag, $taxonomy->name);
          if (!$term || !isset($term->name, $term->taxonomy)) {
            continue;
          }

          $result = wp_set_object_terms($post_id, $term->name, $term->taxonomy, true);
          if (is_wp_error($result)) {
            write_log("Failed to assign term '{$term->name}' to taxonomy '{$taxonomy->name}' on post ID {$post_id}: {$result->get_error_message()}");
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

  /** Pulls the last path segment (e.g. a slug) out of a Passle-supplied URL. */
  protected static function extract_slug_from_url(string $url)
  {
    $path = parse_url($url, PHP_URL_PATH);
    return basename($path ?? $url);
  }

  /** Loops over every Passle shortcode configured in plugin options and syncs each one. */
  protected static function batch_sync_all()
  {
    $passle_shortcodes = OptionsService::get()->passle_shortcodes;

    foreach ($passle_shortcodes as &$passle_shortcode) {
      static::sync_all_by_passle($passle_shortcode);
    }
  }


  /** Builds the API list URL for one Passle shortcode and kicks off paginated syncing for it. */
  public static function sync_all_by_passle(string $passle_shortcode)
  {
    $resource = static::get_resource_instance();

    $url = (new UrlFactory())
      ->path("passlesync/{$resource->name_plural}")
      ->parameters([
        "PassleShortcode" => $passle_shortcode,
        "ItemsPerPage" => "100",
        "IncludeTagGroups" => "true"
      ])
      ->build();

    static::sync_all_paginated($url, 1);
  }


  /**
   * Works out how many pages the API has for this URL and queues one async
   * `passle_{plural}_sync_page` job per page (each processed by sync_page() below),
   * resuming from get_last_synced_page() if a previous run was interrupted.
   */
  protected static function sync_all_paginated(string $url, int $page_number)
  {
    $resource = static::get_resource_instance();

    $last_synced_page = static::get_last_synced_page(); 

    // If sync all has been interrupted, last synced page will give us the last page of synced data before the interruption
    $page_number = $last_synced_page;

    $next_url = call_user_func([$resource->passle_content_service_name, "get_next_url"], $url, 1);
    $response = call_user_func([$resource->passle_content_service_name, "get"], $url);

    // Validate the API response
    if (!isset($response[ucfirst($resource->name_plural)])) {
      throw new Exception("Failed to get data from the API", 500);
    }

    $max_pages = ceil($response["TotalCount"]/$response["PageSize"]);

    while ($page_number <= $max_pages) {

      $next_url = call_user_func([$resource->passle_content_service_name, "get_next_url"], $url, $page_number);
        
      QueueJobAction::execute("passle_{$resource->name_plural}_sync_page", [$next_url, $page_number, $max_pages], $resource->get_schedule_group_name());

      $page_number += 1;
    }

    return;
  }

  /**
   * The per-page worker queued by sync_all_paginated(): fetches this page, validates the
   * response is complete (throwing - which Action Scheduler records as a failed job - if
   * it's missing or truncated), creates/updates every item, runs post_page_sync_hook(),
   * and advances (or, on the last page, resets) the sync-resume checkpoint.
   */
  public static function sync_page(string $url, int $page_number, int $total_pages)
  {
      $resource = static::get_resource_instance();
      $response = call_user_func([$resource->passle_content_service_name, "get"], $url);

      // Validate the API response
      if (!isset($response[ucfirst($resource->name_plural)])) {
        throw new Exception("Failed to get data from the API", 500);
      }

      $items = $response[ucfirst($resource->name_plural)];

      // A page that comes back with fewer items than its own TotalCount/PageSize imply is
      // truncated (e.g. an upstream glitch), not a legitimately short last page. Throwing here
      // - rather than silently continuing - surfaces the failure the same way a hard API error
      // does, and (via post_page_sync_hook) keeps that page out of any run's completed-page count.
      if (isset($response["TotalCount"], $response["PageSize"]) && $response["PageSize"] > 0) {
        $items_before_this_page = $response["PageSize"] * ($page_number - 1);
        $expected_count = max(0, min($response["PageSize"], $response["TotalCount"] - $items_before_this_page));

        if (count($items) < $expected_count) {
          throw new Exception(
            "Passle sync: page {$page_number} of {$total_pages} for {$resource->name_plural} returned " . count($items) . " item(s), expected {$expected_count}.",
            500
          );
        }
      }

      foreach ($items as $item) {
        static::create_or_update($item);
      }

      static::post_page_sync_hook($url, $items, $page_number, $total_pages);

      if ($page_number < $total_pages) {
        static::set_last_synced_page($page_number);
      } else {
        static::set_last_synced_page(1);
      }
  }
}