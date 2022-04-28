<?php

namespace Passle\PassleSync\Controllers;

use Exception;
use Passle\PassleSync\PostTypes\PasslePerson;
use Passle\PassleSync\PostTypes\PasslePost;

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
    update_option(POST_PERMALINK_PREFIX, $params["postPermalinkPrefix"], true);
    update_option(PERSON_PERMALINK_PREFIX, $params["personPermalinkPrefix"], true);

    flush_rewrite_rules();

    return true;
  }
}
