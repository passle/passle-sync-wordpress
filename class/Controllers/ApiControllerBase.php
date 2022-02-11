<?php

namespace Passle\PassleSync\Controllers;

class ApiControllerBase
{
  protected $sync_handler;
  private $plugin_api_key;
  protected $fields = [];
  public $items_being_synced = 0;

  public function __construct()
  {
    $this->plugin_api_key = get_option(PASSLESYNC_PLUGIN_API_KEY);
    $this->api_key = get_option(PASSLESYNC_CLIENT_API_KEY);
    $this->queue = new \SplQueue();
  }

  public function verify_header_api_key($request)
  {
    if ($request->get_header('APIKey') == $this->plugin_api_key) {
      return true;
    }
    return false;
  }

  public function register_route(string $path, string $method, string $func_name)
  {
    register_rest_route(PASSLESYNC_REST_API_BASE, $path, [
      'methods' => $method,
      'callback' => [$this, $func_name],
      'validate_callback' => function ($request) {
        return $this->verify_header_api_key($request);
      },
      'permission_callback' => '__return_true',
    ]);
  }

  public function register_api_settings_routes()
  {
    $this->register_route('/settings/update', 'POST', 'update_api_settings');
  }

  public function update_api_settings($data)
  {
    $json_params = $data->get_json_params();

    if (!isset($json_params)) {
      return new \WP_Error('no_data', 'You must include data to update settings', ['status' => 400]);
    }

    update_option(PASSLESYNC_PLUGIN_API_KEY, $json_params['pluginApiKey'], true);
    update_option(PASSLESYNC_CLIENT_API_KEY, $json_params['clientApiKey'], true);
    update_option(PASSLESYNC_SHORTCODE, $json_params['passleShortcodes'], true);
    return true;
  }
}
