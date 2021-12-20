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
        $result = [];
        foreach (get_declared_classes() as $class_name) {
            if (in_array('Passle\PassleSync\SyncHandlers\ISyncHandler', class_implements($class_name))) {
                array_push($result, $class_name);
            }
        }

        return $result;
    }
}
