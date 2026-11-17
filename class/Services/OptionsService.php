<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Models\Admin\Options;

class OptionsService
{
  public static function init()
  {
    if (!static::get())
    {
        add_option(PASSLESYNC_OPTIONS_KEY, static::get_default_options());
    }
  }

  public static function get(): Options
  {
    $cached_options = get_option(PASSLESYNC_OPTIONS_KEY, static::get_default_options());

    // If the stored options can't be unserialized, get_option returns false. This could happen on sites whose
    // options were stored with the site URL in them (before site_url was excluded from serialization) and were
    // then migrated with a plain-text search/replace. Fall back to the defaults rather than throwing a fatal error.
    if (!($cached_options instanceof Options)) {
      write_log("Could not unserialize the " . PASSLESYNC_OPTIONS_KEY . " option. Falling back to default options.");
      $cached_options = static::get_default_options();
      update_option(PASSLESYNC_OPTIONS_KEY, $cached_options);
    }

    if (!isset($cached_options->include_passle_tag_groups)) {
        $cached_options->include_passle_tag_groups = false;
    }

    return $cached_options;
  }

  public static function set(Options $options, bool $create_rewrite_rules = true)
  {
    update_option(PASSLESYNC_OPTIONS_KEY, $options);

    if ($create_rewrite_rules) {
      $cpts = static::get_resource_cpts();

      foreach ($cpts as $cpt) {
        $cpt::create_rewrite_rules();
      }

      RewriteService::add_preview_rewrite();
    }
  }

  private static function get_default_options(): Options
  {
    return new Options("", wp_generate_uuid4(), [], "p/{{PostShortcode}}/{{PostSlug}}", "u/{{PersonShortcode}}/{{PersonSlug}}", "", true, false, false, false);
  }

  private static function get_resource_cpts()
  {
    $resources = ResourceRegistryService::get_all_instances();

    $cpts = array_map(fn ($resource) => $resource->cpt_name, $resources);

    return $cpts;
  }
}
