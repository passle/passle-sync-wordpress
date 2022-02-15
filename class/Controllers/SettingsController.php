<?php

namespace Passle\PassleSync\Controllers;

use Exception;

class SettingsController extends ControllerBase
{
  public function register_routes()
  {
    $this->register_route("/settings/update", "POST", "update_api_settings");
  }

  public function update_api_settings($request)
  {
    $params = $request->get_params();

    if (!isset($params)) {
      throw new Exception("You must include data to update settings", 400);
    }

    update_option(PASSLESYNC_PLUGIN_API_KEY, $params["pluginApiKey"], true);
    update_option(PASSLESYNC_CLIENT_API_KEY, $params["clientApiKey"], true);
    update_option(PASSLESYNC_SHORTCODE, $params["passleShortcodes"], true);

    return true;
  }
}
