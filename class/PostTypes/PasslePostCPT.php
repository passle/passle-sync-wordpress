<?php

namespace Passle\PassleSync\PostTypes;

use Passle\PassleSync\Services\OptionsService;

class PasslePostCPT
{
  public static function init()
  {
    add_action("init", [self::class, "create_post_type"]);
    add_action("init", [self::class, "create_rewrite_rules"]);
    add_filter("post_type_link", [self::class, "rewrite_post_permalink"], 1, 2);
  }

  public static function create_post_type()
  {
    $labels = [
      "name" => "Passle Posts",
      "singular_name" => "Passle Post",
      "menu_name" => "Passle Posts",
      "name_admin_bar" => "Passle Post",
      "add_new" => "Add New",
      "add_new_item" => "Add New Passle Post",
      "new_item" => "New Passle Post",
      "edit_item" => "Edit Passle Post",
      "view_item" => "View Passle Post",
      "all_items" => "All Passle Posts",
      "search_items" => "Search Passle Posts",
      "parent_item_colon" => "Parent Passle Post",
      "not_found" => "No Passle Posts Found",
      "not_found_in_trash" => "No Passle Posts Found in Trash"
    ];

    $args = [
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
      "menu_icon" => "dashicons-admin-post",
      "capability_type" => "post",
      "capabilities" => [
        "create_posts" => "do_not_allow",
      ],
      "hierarchical" => false,
      "supports" => ["title", "custom-fields"],
      "has_archive" => true,
      "rewrite" => false,
      "query_var" => true,
      "show_in_rest" => true
    ];

    register_post_type(PASSLESYNC_POST_TYPE, $args);
  }

  public static function create_rewrite_rules()
  {
    $post_permalink_prefix = OptionsService::get()->post_permalink_prefix;

    add_rewrite_rule(
      '^' . $post_permalink_prefix . '/([^/]*)/([^/]*)/?$',
      'index.php?post_type=' . PASSLESYNC_POST_TYPE . '&name=$matches[1]',
      'top'
    );
  }

  public static function rewrite_post_permalink($permalink, $post)
  {
    if ($post->post_type !== PASSLESYNC_POST_TYPE) return $permalink;

    $post_shortcode = get_post_meta($post->ID, "post_shortcode", true);
    $post_slug = get_post_meta($post->ID, "post_slug", true);

    $post_permalink_prefix = OptionsService::get()->post_permalink_prefix;
    return home_url($post_permalink_prefix . "/$post_shortcode/$post_slug");
  }
}
