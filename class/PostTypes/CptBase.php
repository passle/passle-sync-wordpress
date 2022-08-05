<?php

namespace Passle\PassleSync\PostTypes;

use Passle\PassleSync\Utils\ResourceClassBase;
use UnexpectedValueException;

abstract class CptBase extends ResourceClassBase
{
  protected abstract static function get_cpt_args(): array;
  protected abstract static function get_permalink_prefix(): string;

  public static function init()
  {
    add_action("init", [static::class, "create_post_type"]);
    add_action("init", [static::class, "create_rewrite_rules"]);
    add_filter("post_type_link", [static::class, "rewrite_post_permalink"], 1, 2);
  }

  public static function create_post_type()
  {
    $resource = static::get_resource_instance();

    $name_singular = "Passle " . ucfirst($resource->display_name_singular);
    $name_plural = "Passle " . ucfirst($resource->display_name_plural);

    $labels = [
      "name" => $name_plural,
      "singular_name" => $name_singular,
      "menu_name" => $name_plural,
      "name_admin_bar" => $name_singular,
      "add_new" => "Add New",
      "add_new_item" => "Add New $name_singular",
      "new_item" => "New $name_singular",
      "edit_item" => "Edit $name_singular",
      "view_item" => "View $name_singular",
      "all_items" => "All $name_plural",
      "search_items" => "Search $name_plural",
      "parent_item_colon" => "Parent $name_singular",
      "not_found" => "No $name_plural Found",
      "not_found_in_trash" => "No $name_plural Found in Trash"
    ];

    $args = array_merge_recursive([
      "labels" => $labels,
      "public" => true,
      "exclude_from_search" => false,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_nav_menus" => true,
      "show_in_menu" => true,
      "show_in_admin_bar" => false,
      "map_meta_cap" => false,
      "menu_position" => 5,
      "capability_type" => "post",
      "capabilities" => [
        "create_posts" => "do_not_allow",
      ],
      "hierarchical" => false,
      "supports" => ["title", "custom-fields"],
      "taxonomies" => ["post_tag"],
      "has_archive" => true,
      "rewrite" => false,
      "query_var" => true,
      "show_in_rest" => true
    ], static::get_cpt_args());

    register_post_type($resource->get_post_type(), $args);
  }

  public static function create_rewrite_rules()
  {
    $resource = static::get_resource_instance();

    $post_permalink_prefix = static::get_permalink_prefix();

    add_rewrite_rule(
      '^' . $post_permalink_prefix . '/([^/]*)/([^/]*)/?$',
      'index.php?post_type=' . $resource->get_post_type() . '&name=$matches[1]',
      'top'
    );
  }

  public static function rewrite_post_permalink($permalink, $post)
  {
    $resource = static::get_resource_instance();

    if ($post->post_type !== $resource->get_post_type()) return $permalink;

    $post_shortcode = get_post_meta($post->ID, $resource->get_meta_shortcode_name(), true);
    $post_slug = get_post_meta($post->ID, $resource->get_meta_slug_name(), true);

    $post_permalink_prefix = static::get_permalink_prefix();
    return home_url($post_permalink_prefix . "/$post_shortcode/$post_slug");
  }
}
