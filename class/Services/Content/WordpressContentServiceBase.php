<?php

namespace Passle\PassleSync\Services\Content;

abstract class WordpressContentServiceBase
{
  protected string $item_type;

  public function __construct(string $item_type)
  {
    $this->item_type = $item_type;
  }

  public function get_items()
  {
    return $this->get_items_by_type($this->item_type);
  }

  public function get_items_by_type(string $item_type)
  {
    $items = get_posts([
      'numberposts'   => -1,
      'post_type'     => [$item_type],
    ]);

    if (empty($items)) {
      return [];
    }

    array_walk($items, function ($item) {
      $item = $this->apply_meta_data_to_item($item);
    });

    return $items;
  }

  public function get_item_by_shortcode(string $shortcode, string $shortcode_property)
  {
    $items = $this->get_items();

    $matching_items = array_filter($items, function ($item) use ($shortcode, $shortcode_property) {
      return $item->{$shortcode_property} === $shortcode;
    });

    if (count($matching_items) > 0) {
      $item = reset($matching_items);
      return $item;
    } else {
      return null;
    }
  }

  public function apply_meta_data_to_item(object $item)
  {
    $meta = get_post_meta($item->ID);

    foreach ($meta as $meta_key => $meta_value) {
      $item->$meta_key = $meta_value[0];
    }

    return $item;
  }

  public function get_or_update_items(string $storage_key, $callback)
  {
    $items = get_option($storage_key);

    if (gettype($items) != "array" || count($items) == 0 || reset($items) == null) {
      $items = $callback();
    }

    return $items;
  }
}
