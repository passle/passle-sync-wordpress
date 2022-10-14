<?php

namespace Passle\PassleSync\Controllers\Resources;

use Passle\PassleSync\Controllers\Resources\ResourceControllerBase;
use Passle\PassleSync\Models\Resources\PersonResource;
use Passle\PassleSync\Services\OptionsService;

class PeopleController extends ResourceControllerBase
{
  const RESOURCE = PersonResource::class;

  public static function filter_entities_before_sync(array $entities)
  {
    $passle_shortcodes = OptionsService::get()->passle_shortcodes;

    $filtered_entities = [];
    foreach ($entities as $entity) {
      foreach ($entity["PassleShortcodes"] as $shortcode) {
        in_array($shortcode, $passle_shortcodes) ? array_push($filtered_entities, $entity) : null;
      }
    }

    $entities = array_unique($filtered_entities);

    return $entities;
  }
}
