<?php

namespace Passle\PassleSync\Controllers;

use Passle\PassleSync\Services\OptionsService;

abstract class ControllerBase
{

  private string $plugin_api_key;

  public function __construct()
  {
    $options = OptionsService::get();
    $this->plugin_api_key = $options->plugin_api_key;
  }

  public abstract function register_routes();

  protected function register_route(string $path, string $method, string $func_name, string $validate_callback = "validate_admin_dashboard_request")
  {
    register_rest_route(PASSLESYNC_REST_API_BASE, $path, [
      "methods" => $method,
      "callback" => [$this, $func_name],
      "validate_callback" => [$this, $validate_callback],
      "permission_callback" => "__return_true",
    ]);
  }

  public function validate_admin_dashboard_request($request): bool
  {
    return $request->get_header("APIKey") == $this->plugin_api_key;
  }

  public function validate_passle_webhook_request($request): bool
  {
    return true; // TODO: Validate sync API key.
  }
}
