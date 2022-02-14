<?php

namespace Passle\PassleSync\Controllers;

abstract class ControllerBase
{

  private string $plugin_api_key;

  public function __construct()
  {
    $this->plugin_api_key = get_option(PASSLESYNC_PLUGIN_API_KEY);
  }

  protected function register_route(string $path, string $method, string $func_name)
  {
    register_rest_route(PASSLESYNC_REST_API_BASE, $path, [
      "methods" => $method,
      "callback" => [$this, $func_name],
      "validate_callback" => [$this, "validate_callback"],
      "permission_callback" => "__return_true",
    ]);
  }

  protected function validate_callback($request): bool
  {
    return $request->get_header("APIKey") == $this->plugin_api_key;
  }
}
