<?php

namespace Passle\PassleSync\Controllers\Resources;

use Passle\PassleSync\Controllers\Resources\ResourceControllerBase;
use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\Services\OptionsService;

class PostsController extends ResourceControllerBase
{
  const RESOURCE = PostResource::class;

  public static function filter_entities_before_sync($entities)
  {
    $passle_shortcodes = OptionsService::get()->passle_shortcodes;

    $entities = array_filter($entities, fn ($entity) => in_array($entity["PassleShortcode"], $passle_shortcodes));

    return $entities;
  }
}
