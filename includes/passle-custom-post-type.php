<?php

/*
 * Hook into the 'init' action so that the function
 * Containing our post type registration is not
 * unnecessarily executed.
 */
add_action('init', 'passle_custom_post_type', 0);

/*
* Creating a function to create our CPT
*/
function passle_custom_post_type()
{
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x('Passle Posts', 'Post Type General Name', 'passlesync'),
        'singular_name'       => _x('Passle Post', 'Post Type Singular Name', 'passlesync'),
        'menu_name'           => __('Passle Posts', 'passlesync'),
        'all_items'           => __('All Passle Posts', 'passlesync'),
        'view_item'           => __('View Passle Post', 'passlesync'),
        'add_new_item'        => __('Add New Passle Post', 'passlesync'),
        'add_new'             => __('Add New', 'passlesync'),
        'edit_item'           => __('Edit Passle Post', 'passlesync'),
        'update_item'         => __('Update Passle Post', 'passlesync'),
        'search_items'        => __('Search Passle Posts', 'passlesync'),
        'not_found'           => __('Not Found', 'passlesync'),
        'not_found_in_trash'  => __('Not found in Trash', 'passlesync'),
    );

    // Set other options for Custom Post Type
    $args = array(
        'label'               => __('PasslePost', 'passlesync'),
        'description'         => __('Passle Posts', 'passlesync'),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array('title', 'editor', 'author', 'featured-image', 'custom-fields',),
        // You can associate this CPT with a taxonomy or custom taxonomy.
        'taxonomies'          => array('assle', 'tags'),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => false,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'        => true,
        'rewrite'             => array('slug' => 'post')
    );

    // Registering your Custom Post Type
    register_post_type('PasslePost', $args);
}
