<?php

namespace Passle\PassleSync\Services\Content;

abstract class WordpressContentServiceBase
{
    public abstract function get_items();

    public function apply_meta_data_to_item(object $item) 
    {
        return $item;
    }

    public function get_items_by_type(string $item_type)
    {
        $items = get_posts(array(
            'numberposts'   => -1,
            'post_type'     => array($item_type),
        ));

        if (empty($items)) {
            return array();
        }

        array_walk($items, function($item) {
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

    public function apply_individual_meta_data_to_item(object $item, array $meta, string $propName, $default)
    {
        if (!empty($meta[$propName])) {
            $item->{$propName} = $meta[$propName][0];
        } else {
            $item->{$propName} = $default;
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
