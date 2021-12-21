<?php

namespace Passle\PassleSync;

use Passle\PassleSync\PostTypes\PasslePost;
use Passle\PassleSync\Services\ApiService;
use Passle\PassleSync\Services\MenuService;

class PassleSync
{
    private $api_service;
    private $menu_service;

    public function __construct(ApiService $api_service, MenuService $menu_service)
    {
        $this->api_service = $api_service;
        $this->menu_service = $menu_service;
    }

    public function initialize()
    {
        // Register API routes
        add_action("rest_api_init", array($this->api_service, "register_api_routes"));

        // Register settings menu
        add_action("admin_menu", array($this->menu_service, "register_menus"));

        // Register post types and additional fields
        new PasslePost();
    }
}
