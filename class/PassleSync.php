<?php

namespace Passle\PassleSync;

use Passle\PassleSync\PostTypes\PasslePost;
use Passle\PassleSync\Controllers\PostsApiController;
use Passle\PassleSync\Controllers\PeopleApiController;
use Passle\PassleSync\Controllers\ApiControllerBase;
use Passle\PassleSync\Services\MenuService;

class PassleSync
{
  private $posts_api_controller;
  private $people_api_controller;
  private $api_controller_base;
  private $menu_service;

  public function __construct(
    PostsApiController $posts_api_controller,
    PeopleApiController $people_api_controller,
    ApiControllerBase $api_controller_base,
    MenuService $menu_service
  ) {
    $this->posts_api_controller = $posts_api_controller;
    $this->people_api_controller = $people_api_controller;
    $this->api_controller_base = $api_controller_base;
    $this->menu_service = $menu_service;
  }

  public function initialize()
  {
    // Register API routes
    add_action("rest_api_init", [$this->posts_api_controller, "register_api_routes"]);
    add_action("rest_api_init", [$this->people_api_controller, "register_api_routes"]);
    add_action("rest_api_init", [$this->api_controller_base, "register_api_settings_routes"]);

    // Register settings menu
    add_action("admin_menu", [$this->menu_service, "register_menus"]);

    // /*
    // * Modify WP queries on the home page or searches
    // * so that they return our new custom post type
    // * as well as the default post type.
    // */
    // // I guess we wouldn't want to do this by default
    // // in case they had something else set
    // add_action( 'pre_get_posts', function ($query) {
    //     if ( $query->is_home() && $query->is_main_query() ) {
    //         $query->set( 'post_type', [ 'post', PASSLESYNC_POST_TYPE ] );
    //     }
    // });

    // Register post types and additional fields
    new PasslePost();
  }
}
