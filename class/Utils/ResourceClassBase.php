<?php

namespace Passle\PassleSync\Utils;

use Passle\PassleSync\Services\ResourceRegistryService;

abstract class ResourceClassBase
{
  const RESOURCE = "";

  protected static function get_resource_instance()
  {
    return ResourceRegistryService::get_resource_instance(static::RESOURCE);
  }
}
