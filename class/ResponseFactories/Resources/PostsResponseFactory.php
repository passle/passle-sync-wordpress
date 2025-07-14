<?php

namespace Passle\PassleSync\ResponseFactories\Resources;

use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\ResponseFactories\Resources\ResourceResponseFactoryBase;

class PostsResponseFactory extends ResourceResponseFactoryBase
{
  const RESOURCE = PostResource::class;

  /**
   * Sort unique models by publishedDate descending, then format publishedDate
   */
  protected static function filter_entities_before_pagination(array $entities)
  {
    usort($entities, fn ($a, $b) => $b["publishedDate"] <=> $a["publishedDate"]);
    array_walk($entities, fn (&$key) => $key["publishedDate"] = gmdate("d/m/Y H:i", $key["publishedDate"]));

    return $entities;
  }
}
