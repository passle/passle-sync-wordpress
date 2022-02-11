<?php

namespace Passle\PassleSync\Services;

use DI\FactoryInterface;

class SyncService
{
  private $factory;

  public function __construct(FactoryInterface $factory)
  {
    $this->factory = $factory;
  }

  public function sync_all()
  {
    foreach ($this->get_sync_handlers() as $handler) {
      $instance = $this->factory->make($handler);
      $instance->sync_all();
    }
  }

  private function get_sync_handlers()
  {
    return array_filter(get_declared_classes(), fn ($x) => in_array('Passle\PassleSync\SyncHandlers\ISyncHandler', class_implements($x)));
  }
}
