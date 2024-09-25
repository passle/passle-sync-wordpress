<?php

namespace Passle\PassleSync\Actions\Resources;

use Passle\PassleSync\Utils\ResourceClassBase;

abstract class ResourceActionBase extends ResourceClassBase
{
  public static function update(string $shortcode)
  {
    $resource = static::get_resource_instance();
    call_user_func([$resource->passle_content_service_name, "fetch_by_shortcode"], $shortcode);
    call_user_func([$resource->sync_handler_name, "sync_many"], [$shortcode]);
  }

  public static function delete(string $shortcode)
  {
    $resource = static::get_resource_instance();
    call_user_func([$resource->sync_handler_name, "delete_many"], [$shortcode]);
    call_user_func([$resource->passle_content_service_name, "update_cache"], array());
  }
}
