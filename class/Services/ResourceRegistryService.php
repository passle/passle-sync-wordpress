<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Models\Resources\PersonResource;
use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\Models\Resources\ResourceBase;

class ResourceRegistryService
{
  const RESOURCES = [
    PostResource::class,
    PersonResource::class,
  ];

  public static function get_all_instances()
  {
    $instances = array_map(fn ($resource) => static::get_resource_instance($resource), static::RESOURCES);

    return $instances;
  }

  public static function get_resource_instance(string $class_name): ResourceBase
  {
    $instance = new $class_name();

    return $instance;
  }
}
