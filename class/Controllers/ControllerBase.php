<?php

namespace Passle\PassleSync\Controllers;

use Passle\PassleSync\Services\OptionsService;
use \WP_REST_Request;

abstract class ControllerBase
{

  private string $plugin_api_key;

  public function __construct()
  {
    $options = OptionsService::get();
    $this->plugin_api_key = $options->plugin_api_key;
  }

  public abstract function register_routes();

  protected function register_route(string $path, string $method, string $func_name, string $permission_callback = "validate_admin_dashboard_request")
  {
    register_rest_route(PASSLESYNC_REST_API_BASE, $path, [
      "methods" => $method,
      "callback" => [$this, $func_name],
      "validate_callback" => "__return_true",
      "permission_callback" => [$this, $permission_callback],
    ]);
  }

  public function validate_admin_dashboard_request(): bool
  {
    return current_user_can("administrator");
  }

  public function validate_passle_webhook_request(WP_REST_Request $request): bool
  {
    return $request->get_header("APIKey") === $this->plugin_api_key;
  }
}
