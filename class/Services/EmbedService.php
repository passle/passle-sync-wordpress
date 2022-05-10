<?php

namespace Passle\PassleSync\Services;

class EmbedService
{
  public static function init()
  {
    add_action("wp_enqueue_scripts", [self::class, "enqueue_scripts"]);
    add_action("wp_enqueue_scripts", [self::class, "enqueue_styles"]);
  }

  public static function enqueue_scripts()
  {
    wp_register_script(
      "passle-remote-hosting-bundle",
      "https://clientweb.passle." . PASSLESYNC_DOMAIN_EXT . "/v1/RemoteHostingBundle",
      ["jquery"],
      false,
      true
    );

    wp_enqueue_script("passle-remote-hosting-bundle");
  }

  public static function enqueue_styles()
  {
    wp_register_style(
      "passle-fontawesome",
      "https://dukb55syzud3u.cloudfront.net/Content/fontawesome/all.min.css?v=5.3.3"
    );

    wp_enqueue_style("passle-fontawesome");
  }
}
