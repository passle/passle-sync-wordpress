<?php

namespace Passle\PassleSync;

use Passle\PassleSync\PostTypes\PasslePost;
use Passle\PassleSync\Services\Api\PostsApiService;
use Passle\PassleSync\Services\Api\PeopleApiService;
use Passle\PassleSync\Services\MenuService;

class PassleSync
{
    private $posts_api_service;
    private $people_api_service;
    private $menu_service;

    public function __construct(PostsApiService $posts_api_service, PeopleApiService $people_api_service, MenuService $menu_service)
    {
        $this->posts_api_service = $posts_api_service;
        $this->people_api_service = $people_api_service;
        $this->menu_service = $menu_service;
    }

    public function initialize()
    {
        // Register API routes
        add_action("rest_api_init", array($this->posts_api_service, "register_api_routes"));
        add_action("rest_api_init", array($this->people_api_service, "register_api_routes"));

        // Register settings menu
        add_action("admin_menu", array($this->menu_service, "register_menus"));

        // /*
        // * Modify WP queries on the home page or searches
        // * so that they return our new custom post type
        // * as well as the default post type.
        // */
        // // I guess we wouldn't want to do this by default
        // // in case they had something else set
        // add_action( 'pre_get_posts', function ($query) {
        //     if ( $query->is_home() && $query->is_main_query() ) {
        //         $query->set( 'post_type', array( 'post', PASSLESYNC_POST_TYPE ) );
        //     }
        // });

        // Register post types and additional fields
        new PasslePost();
    }
}
