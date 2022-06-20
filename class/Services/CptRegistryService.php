<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\PostTypes\PasslePersonCpt;
use Passle\PassleSync\PostTypes\PasslePostCpt;

class CptRegistryService
{
  const CUSTOM_POST_TYPES = [
    PasslePostCpt::class,
    PasslePersonCpt::class,
  ];

  public static function init()
  {
    foreach (static::CUSTOM_POST_TYPES as $cpt) {
      call_user_func([$cpt, "init"]);
    }
  }
}
