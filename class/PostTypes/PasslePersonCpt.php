<?php

namespace Passle\PassleSync\PostTypes;

use Passle\PassleSync\Models\PassleAuthor;
use Passle\PassleSync\Models\Resources\PersonResource;
use Passle\PassleSync\Services\OptionsService;

class PasslePersonCpt extends CptBase
{
  const RESOURCE = PersonResource::class;

  protected static function get_cpt_args(): array
  {
    return [
      "menu_icon" => "dashicons-admin-users",
    ];
  }

  protected static function get_permalink_template(): string
  {
    return OptionsService::get()->person_permalink_template;
  }

  public static function rewrite_permalink($resource, $post): string
  {
    $passle_author = new PassleAuthor($post);

    if (!is_null($passle_author)) {
      return $passle_author->profile_url;
    } else {
      return parent::rewrite_permalink($resource, $post);
    }
  }
}
