<?php

namespace Passle\PassleSync\Services\Content\Passle;

use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\Utils\UrlFactory;

class PasslePostsContentService extends PassleContentServiceBase
{
  const RESOURCE = PostResource::class;

  public static function fetch_preview(string $post_shortcode)
  {
    $url = (new UrlFactory())
      ->path("passlesync/posts/{$post_shortcode}")
      ->build();

    return static::get($url);
  }
}
