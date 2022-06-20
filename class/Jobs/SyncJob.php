<?php

namespace Passle\PassleSync\Jobs;

class SyncJob
{
  public static function init(string $resource_name, string $handler)
  {
    add_action("passle_{$resource_name}_sync_all", [$handler, "sync_all"]);
    add_action("passle_{$resource_name}_sync_many", [$handler, "sync_many"]);
    add_action("passle_{$resource_name}_delete_all", [$handler, "delete_all"]);
    add_action("passle_{$resource_name}_delete_many", [$handler, "delete_many"]);
  }
}
