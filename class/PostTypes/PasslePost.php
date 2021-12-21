<?php

namespace Passle\PassleSync\PostTypes;

class PasslePost
{
    public function __construct()
    {
        add_action("init", array($this, "create_post_type"));
    }

    function create_post_type()
    {
        // TODO: Create any/all custom taxonomies in another method.

        $labels = array(
            'name' => 'Passle Posts',
            'singular_name' => 'Passle Post',
            'menu_name' => 'Passle Posts',
            'name_admin_bar' => 'Passle Post',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Passle Post',
            'new_item' => 'New Passle Post',
            'edit_item' => 'Edit Passle Post',
            'view_item' => 'View Passle Post',
            'all_items' => 'All Passle Posts',
            'search_items' => 'Search Passle Posts',
            'parent_item_colon' => 'Parent Passle Post',
            'not_found' => 'No Passle Posts Found',
            'not_found_in_trash' => 'No Passle Posts Found in Trash'
        );

        $args = array(
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
            'capabilities' => array(
                'create_posts' => 'do_not_allow',
                // 'edit_posts' => 'do_not_allow',
            ),
            'hierarchical' => false,
            'supports' => array('title', 'custom-fields'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'passle-posts'),
            'query_var' => true,
            'show_in_rest' => true
        );

        register_post_type(PASSLESYNC_POST_TYPE, $args);
    }
}
