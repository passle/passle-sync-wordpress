<?php

namespace Passle\PassleSync\PostTypes;

class PasslePerson
{
  public function __construct()
  {
    add_action("init", [$this, "create_post_type"]);
  }

  function create_post_type()
  {
    // TODO: Create any/all custom taxonomies in another method.

    $labels = [
      'name' => 'Passle Authors',
      'singular_name' => 'Passle Author',
      'menu_name' => 'Passle Authors',
      'name_admin_bar' => 'Passle Author',
      'add_new' => 'Add New',
      'add_new_item' => 'Add New Passle Author',
      'new_item' => 'New Passle Author',
      'edit_item' => 'Edit Passle Author',
      'view_item' => 'View Passle Author',
      'all_items' => 'All Passle Authors',
      'search_items' => 'Search Passle Authors',
      'parent_item_colon' => 'Parent Passle Author',
      'not_found' => 'No Passle Authors Found',
      'not_found_in_trash' => 'No Passle Authors Found in Trash'
    ];

    $args = [
      'labels' => $labels,
      'public' => true,
      'exclude_from_search' => false,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_nav_menus' => true,
      'show_in_menu' => true,
      'show_in_admin_bar' => false,
      'map_meta_cap' => false,
      'menu_position' => 5,
      'menu_icon' => 'dashicons-admin-appearance',
      'capability_type' => 'post',
      'capabilities' => [
        'create_posts' => 'do_not_allow',
        // 'edit_posts' => 'do_not_allow',
      ],
      'hierarchical' => false,
      'supports' => ['title', 'custom-fields'],
      'has_archive' => true,
      'rewrite' => ['slug' => 'passle-authors'],
      'query_var' => true,
      'show_in_rest' => true
    ];

    register_post_type(PASSLESYNC_AUTHOR_TYPE, $args);
  }
}
