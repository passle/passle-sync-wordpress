<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Controllers\FeaturedPostController;
use Passle\PassleSync\Controllers\SettingsController;

class RouteRegistryService
{
  const NON_RESOURCE_CONTROLLERS = [
    SettingsController::class,
    FeaturedPostController::class,
  ];

  public static function init()
  {
    $controllers = array_merge(static::NON_RESOURCE_CONTROLLERS, static::get_resource_controllers());

    foreach ($controllers as $controller) {
      add_action("rest_api_init", [$controller, "init"]);
    }
  }

  private static function get_resource_controllers()
  {
    $resources = ResourceRegistryService::get_all_instances();

    $controllers = array_map(fn ($resource) => $resource->controller_name, $resources);

    return $controllers;
  }
}
