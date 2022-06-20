<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Jobs\SyncJob;

class SchedulerService
{
  public static function init()
  {
    foreach (ResourceRegistryService::get_all_instances() as $resource) {
      SyncJob::init($resource->name_plural, $resource->sync_handler_name);
    }
  }

  public static function queue($hook, $args = [], $group = "")
  {
    return as_enqueue_async_action($hook, $args, $group);
  }

  public static function get_scheduled_actions($args = [], $return_format = OBJECT)
  {
    return as_get_scheduled_actions($args, $return_format);
  }
}
