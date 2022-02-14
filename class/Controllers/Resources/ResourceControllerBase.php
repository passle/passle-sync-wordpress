<?php

namespace Passle\PassleSync\Controllers\Resources;

use Passle\PassleSync\Controllers\ControllerBase;

class ResourceControllerBase extends ControllerBase
{
  protected string $resource_url;

  private string $plugin_api_key;

  public function __construct(string $resource_url)
  {
    $this->plugin_api_key = get_option(PASSLESYNC_PLUGIN_API_KEY);
    $this->resource_url = $resource_url;
  }

  public function register_routes()
  {
    $this->register_route("/{$this->resource_url}", "GET", "get_all");
    $this->register_route("/{$this->resource_url}/sync-all", "POST", "sync_all");
    $this->register_route("/{$this->resource_url}/delete-all", "POST", "delete_all");
    $this->register_route("/{$this->resource_url}/sync-many", "POST", "sync_many");
    $this->register_route("/{$this->resource_url}/delete-many", "POST", "delete_many");
  }

  protected function validate_callback($request): bool
  {
    return $request->get_header("APIKey") == $this->plugin_api_key;
  }
}
