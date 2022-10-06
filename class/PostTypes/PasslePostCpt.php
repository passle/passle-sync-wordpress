<?php

namespace Passle\PassleSync\PostTypes;

use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\Services\OptionsService;

class PasslePostCpt extends CptBase
{
  const RESOURCE = PostResource::class;

  protected static function get_cpt_args(): array
  {
    return [
      "menu_icon" => "dashicons-admin-post",
      "taxonomies" => ["post_tag"],
    ];
  }

  protected static function get_permalink_prefix(): string
  {
    return OptionsService::get()->post_permalink_prefix;
  }
}
