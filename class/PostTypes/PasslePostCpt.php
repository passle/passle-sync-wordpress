<?php

namespace Passle\PassleSync\PostTypes;

use Passle\PassleSync\Models\Resources\PostResource;
use Passle\PassleSync\Services\OptionsService;

class PasslePostCpt extends CptBase
{
  const RESOURCE = PostResource::class;

  protected static function get_cpt_args(): array
  {
    $args = [
       "menu_icon" => "dashicons-admin-post"
    ];

    if (OptionsService::get()->include_passle_tag_groups) {
        $args["taxonomies"] = ["tag_group", "post_tag"];
    } else {
        $args["taxonomies"] = ["post_tag"];
    }

    return $args;
  }

  protected static function get_permalink_template(): string
  {
    return OptionsService::get()->post_permalink_template;
  }
}
