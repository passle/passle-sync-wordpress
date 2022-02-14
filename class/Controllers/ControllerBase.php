<?php

namespace Passle\PassleSync\Controllers;

abstract class ControllerBase
{
  protected function register_route(string $path, string $method, string $func_name)
  {
    register_rest_route(PASSLESYNC_REST_API_BASE, $path, [
      "methods" => $method,
      "callback" => [$this, $func_name],
      "validate_callback" => [$this, "validate_callback"],
      "permission_callback" => "__return_true",
    ]);
  }

  protected abstract function validate_callback($request): bool;
}
