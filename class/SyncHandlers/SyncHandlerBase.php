<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Services\PassleContentService;
use Passle\PassleSync\Utils\Utils;

abstract class SyncHandlerBase
{
  protected $passle_content_service;

  public function __construct(PassleContentService $passle_content_service)
  {
    $this->passle_content_service = $passle_content_service;
  }

  protected abstract function sync_all_impl();
  protected abstract function sync_one_impl(array $data);
  protected abstract function delete_all_impl();
  protected abstract function delete_one_impl(array $data);
  protected abstract function sync(?object $entity, array $data);
  protected abstract function delete(object $entity);

  public function sync_all()
  {
    try {
      return $this->sync_all_impl();
    } catch (\Exception $ex) {
      error_log("Failed to sync all items: {$ex->getMessage()}");
    }
  }

  public function sync_many(array $data)
  {
    foreach ($data as $item) {
      $this->sync_one($item);
    }
  }

  public function sync_one(array $data)
  {
    try {
      return $this->sync_one_impl($data);
    } catch (\Exception $ex) {
      error_log("Failed to sync item: {$ex->getMessage()}");
    }
  }

  public function delete_all()
  {
    try {
      return $this->delete_all_impl();
    } catch (\Exception $ex) {
      error_log("Failed to delete all items: {$ex->getMessage()}");
    }
  }

  public function delete_many(array $data)
  {
    foreach ($data as $item) {
      $this->delete_one($item);
    }
  }

  public function delete_one(array $data)
  {
    try {
      return $this->delete_one_impl($data);
    } catch (\Exception $ex) {
      error_log("Failed to delete item: {$ex->getMessage()}");
    }
  }

  public function compare_items(array $passle_items, array $existing_items, string $passle_item_shortcode_property, string $existing_item_shortcode_property)
  {
    $passle_shortcodes = Utils::array_select($passle_items, $passle_item_shortcode_property);

    $existing_shortcodes = [];
    // Create a dict for easier access later
    $existing_items_by_shortcode = [];

    foreach ($existing_items as $item) {
      $item_shortcode = $item->{$existing_item_shortcode_property}[0];
      array_push($existing_shortcodes, $item_shortcode);
      $existing_items_by_shortcode[$item_shortcode] = $item;
    }

    $all_shortcodes = array_unique(array_merge($passle_shortcodes, $existing_shortcodes));

    $shortcodes_to_add = array_filter($passle_shortcodes, fn ($shortcode) => !in_array($shortcode, $existing_shortcodes));
    $shortcodes_to_remove = array_filter($existing_shortcodes, fn ($shortcode) => !in_array($shortcode, $passle_shortcodes));
    $shortcodes_to_update = array_filter($all_shortcodes, fn ($shortcode) => !in_array($shortcode, $shortcodes_to_add) && !in_array($shortcode, $shortcodes_to_remove));

    $response = [
      "added" => [],
      "updated" => [],
      "removed" => [],
    ];

    // Add
    $items_to_add = array_filter($passle_items, fn ($item) => in_array($item[$passle_item_shortcode_property], $shortcodes_to_add));
    foreach ($items_to_add as $item) {
      array_push($response["added"], $this->sync(null, $item));
    }

    // Update
    $items_to_update = array_filter($passle_items, fn ($item) => in_array($item[$passle_item_shortcode_property], $shortcodes_to_update));
    foreach ($items_to_update as $item) {
      $existing_item = $existing_items_by_shortcode[$item[$passle_item_shortcode_property]];
      array_push($response["updated"], $this->sync($existing_item, $item));
    }

    // Remove
    $items_to_remove = array_filter($existing_items, fn ($item) => in_array($item->{$existing_item_shortcode_property}, $shortcodes_to_remove));
    foreach ($items_to_remove as $item) {
      array_push($response["removed"], $this->delete($item));
    }

    return $response;
  }

  protected function delete_item(int $id)
  {
    return wp_delete_post($id, true);
  }

  protected function insert_post(array $postarr, $wp_error = \false, $fire_after_hooks = \true)
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

    return $post_id;
  }
}
