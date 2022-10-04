<?php

namespace Passle\PassleSync\Controllers;

class HealthCheckController extends ControllerBase
{
  const RESOURCE_NAME = "health-check";

  public static function init()
  {
    $resource_name = static::RESOURCE_NAME;

    static::register_route("/{$resource_name}", "GET", "handle", "validate_passle_webhook_request");
  }

  public static function handle()
  {
    return true;
  }
}
