<?php

/*
 * Modify WP queries on the home page or searches
 * so that they return our new custom post type
 * as well as the default post type.
 */

add_action( 'rest_api_init', 'passle_register_rest_api' );
function passle_register_rest_api() {

    // http://wordpress.example.com/wp-json/passlesync/v1/posts
    register_rest_route( 'passlesync/v1', '/posts', array(
        'methods' => 'GET',
        'callback' => 'get_passle_posts',
    ) );

    // http://wordpress.example.com/wp-json/passlesync/v1/post/0
    register_rest_route( 'passlesync/v1', '/post/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_passle_post_by_id',
    ) );

    // http://wordpress.example.com/wp-json/passlesync/v1/post/update
    register_rest_route( 'passlesync/v1', '/post/update', array(
        'methods' => 'POST',
        'callback' => 'update_passle_post',
    ) );

}

/*
 * Return the list of all posts
 *
 * @param array $data Options for the function.
 * @return array|null The list of posts
 */
function get_passle_posts( $data ) {

    $posts = get_posts( array(
        'numberposts'   => -1,
        'post_type'     => array( 'post', 'PasslePost'),
    ) );

    if ( empty( $posts ) ) {
        return null;
    }

    return $posts;

}

/*
 * Return a post by id
 *
 * @param array $data Options for the function.
 * @return object|null The post that matches the given id
 */
function get_passle_post_by_id( $data ) {

    $posts = get_posts( array(
        'ID'            => $data['id'],
        'numberposts'   => 1,
        'post_type'     => array( 'post', 'PasslePost'),
    ) );

    if ( empty( $posts ) ) {
        return null;
        // return new WP_Error( 'no_id', 'Invalid id', array( 'status' => 404 ) );
    }

    return $posts[0];

}

/*
 * Updates a Passle post, or creates a new one
 *
 * @param array $data Options for the function.
 * @return int|error The id of the post created, or an error message
 */
function update_passle_post( $data ) {

    $post_data = $data->get_json_params();

    if ( empty( $post_data ) ) {
        return new WP_Error( 'no_data', 'You must include data to create a post', array( 'status' => 400 ) );
    }

    if ( empty( $post_data['PostTitle'] ) ) {
        return new WP_Error( 'no_title', 'You must include a post title', array( 'status' => 400 ) );
    }

    if ( empty( $post_data['PostContentHtml'] ) ) {
        return new WP_Error( 'no_content', 'You must include post content', array( 'status' => 400 ) );
    }

    // TODO: Find if there's an existing post with this shortcode
    // Update it, if so

    $new_post = array(
        'post_title'        => $post_data['PostTitle'],
        'post_date'         => $post_data['PublishedDate'],
        'post_type'         => 'PasslePost',
        'post_content'      => $post_data['PostContentHtml'],
        'post_status'       => 'publish',
        'comment_status'    => 'closed',
        'meta_input'    => array(
            'post_shortcode'    => $post_data['PostShortcode'],
            'passle_shortcode'  => $post_data['PassleShortcode']
        )
    );

    $pid = wp_insert_post( $new_post, true );

    return $pid;

}
