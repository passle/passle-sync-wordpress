<?php
// This file enqueues scripts and styles

defined("ABSPATH") or exit;

add_action("init", function () {

  add_filter("script_loader_tag", function ($tag, $handle) {
    if (!preg_match("/^passle-/", $handle)) {
      return $tag;
    }
    return str_replace(" src", " async defer src", $tag);
  }, 10, 2);

  add_action("admin_enqueue_scripts", function () {
    $root_path = plugin_dir_url(__FILE__) . "dist/";
    $asset_manifest = json_decode(file_get_contents(PASSLESYNC_ASSET_MANIFEST), true);


    if (isset($asset_manifest["main.css"])) {
      wp_enqueue_style("passle", $root_path . $asset_manifest["main.css"]);
    }

    if (isset($asset_manifest["runtime~main.js"])) {
      wp_enqueue_script("passle-runtime", $root_path . $asset_manifest["runtime~main.js"], [], null, true);
    }

    wp_enqueue_script("passle-main", $root_path . $asset_manifest["main.js"], [], null, true);

    foreach ($asset_manifest as $key => $value) {
      if (preg_match("@static/js/(.*)\.chunk\.js@", $key, $matches)) {
        if ($matches && is_array($matches) && count($matches) === 2) {
          $name = "passle-" . preg_replace("/[^A-Za-z0-9_]/", "-", $matches[1]);
          wp_enqueue_script($name, $root_path . $value, ["passle-main"], null, true);
        }
      }

      if (preg_match("@static/css/(.*)\.chunk\.css@", $key, $matches)) {
        if ($matches && is_array($matches) && count($matches) == 2) {
          $name = "passle-" . preg_replace("/[^A-Za-z0-9_]/", "-", $matches[1]);
          wp_enqueue_style($name, $root_path . $value, ["passle"], null);
        }
      }
    }
  });
});
