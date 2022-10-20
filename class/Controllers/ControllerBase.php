<?php

namespace Passle\PassleSync\Controllers;

use Exception;
use \WP_REST_Request;
use Passle\PassleSync\Services\OptionsService;

abstract class ControllerBase
{
  public static abstract function init();

  protected static function register_route(string $path, string $method, string $func_name, string $permission_callback = "validate_admin_dashboard_request")
  {
    register_rest_route(PASSLESYNC_REST_API_BASE, $path, [
      "methods" => $method,
      "callback" => [static::class, $func_name],
      "validate_callback" => "__return_true",
      "permission_callback" => [static::class, $permission_callback],
    ]);
  }

  protected static function get_required_parameter(WP_REST_Request $request, string $parameter_name)
  {
    $data = $request->get_json_params();

    $parameter = $data[$parameter_name] ?? array();

    return $parameter;
  }

  public static function validate_admin_dashboard_request(): bool
  {
    return current_user_can("administrator");
  }

  public static function validate_passle_webhook_request(WP_REST_Request $request): bool
  {
    return $request->get_header("APIKey") === OptionsService::get()->plugin_api_key;
  }
}
