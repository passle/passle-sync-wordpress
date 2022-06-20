<?php

namespace Passle\PassleSync\Actions;

use Passle\PassleSync\Models\Resources\ResourceBase;

class RefreshAllAction
{
  public static function execute(ResourceBase $resource)
  {
    $resource_passle_content_service = $resource->passle_content_service_name;

    call_user_func([$resource_passle_content_service, "fetch_all"]);
  }
}
