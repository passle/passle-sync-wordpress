<?php

namespace Passle\PassleSync\Actions;

use Passle\PassleSync\Services\SchedulerService;

class QueueJobAction
{
  public static function execute($hook, $args = [], $group = "")
  {
    return SchedulerService::queue($hook, $args, $group);
  }
}
