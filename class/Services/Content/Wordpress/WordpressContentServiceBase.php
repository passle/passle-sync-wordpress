<?php

namespace Passle\PassleSync\Services\Content\Wordpress;

use Passle\PassleSync\Utils\ResourceClassBase;

abstract class WordpressContentServiceBase extends ResourceClassBase
{
  public static function fetch_entities(array $shortcodes = [])
  {
    $resource = static::get_resource_instance();

    $query = [
      "numberposts" => -1,
      "post_type" => [$resource->get_post_type()],
    ];

    if (!empty($shortcodes)) {
      $query["meta_query"] = [
        [
          "key" => $resource->get_meta_shortcode_name(),
          "value" => $shortcodes,
          "compare" => "IN",
        ],
      ];
    }

    $entities = get_posts($query);

    array_walk($entities, [static::class, "apply_meta_to_entity"]);

    return $entities;
  }

  private static function apply_meta_to_entity(object &$entity)
  {
    $meta = get_post_meta($entity->ID);

    foreach ($meta as $key => $value) {
      $entity->{$key} = $value;
    }
  }
}
