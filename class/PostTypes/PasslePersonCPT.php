<?php

namespace Passle\PassleSync\PostTypes;

class PasslePersonCPT
{
  public function __construct()
  {
    add_action("init", [$this, "create_post_type"]);
    add_action("init", [$this, "create_rewrite_rules"]);
    add_filter("post_type_link", [$this, "rewrite_post_permalink"], 1, 2);
  }

  function create_post_type()
  {
    $labels = [
      "name" => "Passle Authors",
      "singular_name" => "Passle Author",
      "menu_name" => "Passle Authors",
      "name_admin_bar" => "Passle Author",
      "add_new" => "Add New",
      "add_new_item" => "Add New Passle Author",
      "new_item" => "New Passle Author",
      "edit_item" => "Edit Passle Author",
      "view_item" => "View Passle Author",
      "all_items" => "All Passle Authors",
      "search_items" => "Search Passle Authors",
      "parent_item_colon" => "Parent Passle Author",
      "not_found" => "No Passle Authors Found",
      "not_found_in_trash" => "No Passle Authors Found in Trash"
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
      "menu_icon" => "dashicons-admin-users",
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

    register_post_type(PASSLESYNC_AUTHOR_TYPE, $args);
  }

  function create_rewrite_rules()
  {
    $person_permalink_prefix = get_option(PASSLESYNC_PERSON_PERMALINK_PREFIX);

    add_rewrite_rule(
      '^' . $person_permalink_prefix . '/([^/]*)/([^/]*)/?$',
      'index.php?post_type=' . PASSLESYNC_AUTHOR_TYPE . '&name=$matches[1]',
      'top'
    );

    flush_rewrite_rules(); // TODO: Remove this before committing.
  }

  function rewrite_post_permalink($permalink, $post)
  {
    if ($post->post_type !== PASSLESYNC_AUTHOR_TYPE) return $permalink;

    $post_shortcode = get_post_meta($post->ID, "post_shortcode", true);
    $post_slug = get_post_meta($post->ID, "post_slug", true);

    $person_permalink_prefix = get_option(PASSLESYNC_PERSON_PERMALINK_PREFIX);
    return home_url($person_permalink_prefix . "/$post_shortcode/$post_slug");
  }
}
