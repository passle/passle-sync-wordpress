<?php

namespace Passle\PassleSync\Services\Content;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

class WordpressContentServiceBase
{
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

        $matching_items = array_filter($items, function ($item) use ($data) {
            return $item->{$shortcode_property} === $shortcode;
        });

        if (count($matching_items) > 0) {
            $item = reset($matching_items);
            return $item;
        } else {
            return null;
        }
    }

    public function apply_individual_meta_data_to_post(object $post, array $meta, string $propName, $default)
    {
        if (!empty($meta[$propName])) {
            $post->{$propName} = $meta[$propName][0];
        } else {
            $post->{$propName} = $default;
        }
        return $post;
    }

    public function get_or_update_items(string $storage_key, $callback)
    {
        $items = get_option($storage_key);
        
        if (gettype($items) != "array" || count($items) == 0 || reset($items) == null) {
            $items = $callback();
        }

        return $items;
    }

    public function create_new_blank_item(string $item_type)
    {
        $new_item = array(
            'post_type'     => $item_type,
            'post_status'   => 'publish',
            'post_author'   => 1 //TODO: What's this value?
        );

        $id = wp_insert_post($new_item);
        $new_item->ID = $id;
        return $new_item;
    }

    public function delete_item(int $id)
    {        
        return wp_delete_post($id, true);
    }
}
