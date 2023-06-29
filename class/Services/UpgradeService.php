<?php

namespace Passle\PassleSync\Services;

class UpgradeService
{
  private static $migrations = [
    "1.2.0" => [UpgradeService::class, "migrate_1_2_0"]
  ];

  public static function init()
  {
    add_action("plugins_loaded", [static::class, "run_migrations"]);
  }

  public static function run_migrations()
  {
    $version = static::get_plugin_version();

    foreach (static::$migrations as $migration_version => $migration_handler) {
      if (!version_compare($version, $migration_version, "<")) {
        break;
      }

      $migration_handler();
      $version = $migration_version;
    }

    update_option("passlesync_version", $version);
  }

  private static function get_plugin_version()
  {
    $plugin_version = get_option("passlesync_version", "0.0.0");
    return $plugin_version;
  }

  /*
   * Migrations
   */

  private static function migrate_1_2_0()
  {
    // In version 1.2.0 we changed the permalink structure to use a template
    // instead of a prefix. This migration will update the saved options to
    // use the new template structure.

    /** @var Options $saved_options */
    $saved_options = get_option(PASSLESYNC_OPTIONS_KEY);
    if (!$saved_options) {
      return;
    }

    $saved_options->post_permalink_template = $saved_options->post_permalink_prefix . "/{{PostShortcode}}/{{PostSlug}}";
    $saved_options->person_permalink_template = $saved_options->person_permalink_prefix . "/{{PersonShortcode}}/{{PersonSlug}}";

    unset($saved_options->post_permalink_prefix);
    unset($saved_options->person_permalink_prefix);

    update_option(PASSLESYNC_OPTIONS_KEY, $saved_options);
  }
}
