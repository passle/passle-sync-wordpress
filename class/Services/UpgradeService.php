<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Models\Admin\Options;

class UpgradeService
{
  private static $migrations = [
    "1.2.0" => [UpgradeService::class, "migrate_1_2_0"],
    "3.1.1" => [UpgradeService::class, "migrate_3_1_1"],
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
        continue;
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

    if ($saved_options->post_permalink_prefix) {
        $saved_options->post_permalink_template = $saved_options->post_permalink_prefix . "/{{PostShortcode}}/{{PostSlug}}";
    } else {
        $saved_options->post_permalink_template = "p/{{PostShortcode}}/{{PostSlug}}";
    }

    if ($saved_options->person_permalink_prefix) {
        $saved_options->person_permalink_template = $saved_options->person_permalink_prefix . "/{{PersonShortcode}}/{{PersonSlug}}";
    } else {
        $saved_options->person_permalink_template = "u/{{PersonShortcode}}/{{PersonSlug}}";
    }
    
    unset($saved_options->post_permalink_prefix);
    unset($saved_options->person_permalink_prefix);

    update_option(PASSLESYNC_OPTIONS_KEY, $saved_options);
  }

  private static function migrate_3_1_1()
  {
    // In version 3.1.1 we stopped storing site_url in the serialized options, so that a plain-text
    // search/replace of the site URL during a migration can't corrupt them. Re-save the options so the
    // stored value no longer contains the URL. update_option() is a no-op here, since the old and new values
    // serialize identically once loaded, so the stored value is replaced directly.
    $saved_options = get_option(PASSLESYNC_OPTIONS_KEY);
    if (!($saved_options instanceof Options)) {
      return;
    }

    global $wpdb;
    $wpdb->update(
      $wpdb->options,
      ["option_value" => maybe_serialize($saved_options)],
      ["option_name" => PASSLESYNC_OPTIONS_KEY]
    );

    wp_cache_delete(PASSLESYNC_OPTIONS_KEY, "options");
    wp_cache_delete("alloptions", "options");
  }
}
