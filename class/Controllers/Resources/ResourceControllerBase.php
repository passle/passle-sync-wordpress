<?php

namespace Passle\PassleSync\Controllers\Resources;

use Passle\PassleSync\Controllers\ControllerBase;

class ResourceControllerBase extends ControllerBase
{
  protected string $resource_url;

  public function __construct(string $resource_url)
  {
    parent::__construct();
    $this->resource_url = $resource_url;
  }

  public function register_routes()
  {
    $this->register_route("/{$this->resource_url}", "GET", "get_all");
    $this->register_route("/{$this->resource_url}/sync-all", "POST", "sync_all");
    $this->register_route("/{$this->resource_url}/delete-all", "POST", "delete_all");
    $this->register_route("/{$this->resource_url}/sync-many", "POST", "sync_many");
    $this->register_route("/{$this->resource_url}/delete-many", "POST", "delete_many");
    $this->register_route("/{$this->resource_url}/refresh-all", "GET", "refresh_all");
  }
}
