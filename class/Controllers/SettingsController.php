<?php

namespace Passle\PassleSync\Controllers;

use Exception;
use \WP_REST_Request;
use Passle\PassleSync\Models\Admin\Options;
use Passle\PassleSync\Services\OptionsService;

class SettingsController extends ControllerBase
{
  public function register_routes()
  {
    $this->register_route("/settings/update", "POST", "update_api_settings");
  }

  public function update_api_settings(WP_REST_Request $request)
  {
    $params = $request->get_params();

    if (!isset($params)) {
      throw new Exception("You must include data to update settings", 400);
    }

    $options = new Options(
      $params["passleApiKey"],
      $params["pluginApiKey"],
      $params["passleShortcodes"],
      $params["postPermalinkPrefix"],
      $params["personPermalinkPrefix"],
    );

    OptionsService::set($options);

    return $options;
  }
}
