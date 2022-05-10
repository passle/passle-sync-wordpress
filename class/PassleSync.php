<?php

namespace Passle\PassleSync;

use Passle\PassleSync\Controllers\Resources\PostsController;
use Passle\PassleSync\Controllers\Resources\PeopleController;
use Passle\PassleSync\Controllers\SettingsController;
use Passle\PassleSync\PostTypes\PasslePostCPT;
use Passle\PassleSync\PostTypes\PasslePersonCPT;
use Passle\PassleSync\Services\EmbedService;
use Passle\PassleSync\Services\MenuService;
use Passle\PassleSync\Services\OptionsService;

class PassleSync
{
  private $posts_controller;
  private $people_controller;
  private $settings_controller;
  private $menu_service;

  public function __construct(
    PostsController $posts_controller,
    PeopleController $people_controller,
    SettingsController $settings_controller,
    MenuService $menu_service
  ) {
    $this->posts_controller = $posts_controller;
    $this->people_controller = $people_controller;
    $this->settings_controller = $settings_controller;
    $this->menu_service = $menu_service;
  }

  public function initialize()
  {
    add_action("rest_api_init", [$this->posts_controller, "register_routes"]);
    add_action("rest_api_init", [$this->people_controller, "register_routes"]);
    add_action("rest_api_init", [$this->settings_controller, "register_routes"]);

    register_activation_hook(__FILE__, [$this, "activate"]);
    register_deactivation_hook(__FILE__, [$this, "deactivate"]);

    add_action("admin_menu", [$this->menu_service, "register_menus"]);

    EmbedService::init();
    OptionsService::init();
    PasslePostCPT::init();
    PasslePersonCPT::init();
  }

  public function activate()
  {
    flush_rewrite_rules();
  }

  public function deactivate()
  {
    flush_rewrite_rules();
  }
}
