<?php

/*
 * Modify WP queries on the home page or searches
 * so that they return our new custom post type
 * as well as the default post type.
 */

// I guess we wouldn't want to do this by default
// in case they had something else set
add_action( 'pre_get_posts', 'passle_modify_home_page_query' );
function passle_modify_home_page_query($query) {

    if ( $query->is_home() && $query->is_main_query() ) {
        $query->set( 'post_type', array( 'post', 'PasslePost' ) );
    }

}
