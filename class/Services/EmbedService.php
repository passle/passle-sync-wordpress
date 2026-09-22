<?php

namespace Passle\PassleSync\Services;

class EmbedService
{
  public static function init()
  {
    add_action("wp_enqueue_scripts", [static::class, "enqueue_scripts"]);
    add_filter("script_loader_tag", [static::class, "add_crossorigin_attribute"], 10, 2);
  }

  public static function enqueue_scripts()
  {
    wp_register_script(
      "passle-remote-hosting-bundle",
      "https://clientweb.passle." . PASSLESYNC_DOMAIN_EXT . "/v1/RemoteHostingBundle",
      [],
      false,
      true
    );

    wp_enqueue_script("passle-remote-hosting-bundle");
  }

  public static function add_crossorigin_attribute($tag, $handle)
  {
    if ($handle !== "passle-remote-hosting-bundle") {
      return $tag;
    }

    return str_replace(" src=", ' crossorigin="anonymous" src=', $tag);
  }
}
