<?php

namespace Passle\PassleSync\Controllers;

use Exception;
use \WP_REST_Request;
use Passle\PassleSync\Models\Admin\Options;
use Passle\PassleSync\Services\OptionsService;

class SettingsController extends ControllerBase
{
  public static function init()
  {
    static::register_route("/settings/update", "POST", "update_api_settings");
  }

  public static function update_api_settings(WP_REST_Request $request)
  {
    $params = $request->get_params();

    if (!isset($params)) {
      throw new Exception("You must include data to update settings", 400);
    }

    $options = new Options(
      $params["passleApiKey"],
      $params["pluginApiKey"],
      $params["passleShortcodes"],
      $params["postPermalinkTemplate"],
      $params["personPermalinkTemplate"],
      $params["previewPermalinkTemplate"],
      $params["simulateRemoteHosting"],
      $params["includePasslePostsOnHomePage"],
      $params["includePasslePostsOnTagPage"],
      PASSLESYNC_DOMAIN_EXT,
      get_site_url(),
    );

    OptionsService::set($options);

    return $options;
  }
}
