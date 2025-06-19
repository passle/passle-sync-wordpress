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
    
    if (!isset($cached_options->enable_debug_logging)) {
      $cached_options->enable_debug_logging = true; 
    }

    // This is needed as options contain home_url and causes annoying issues when migrating from different environments
    // with the site_url property still holding the value from the old environment
    if ($cached_options->site_url != home_url()) {
        $cached_options->site_url = home_url();
        update_option(PASSLESYNC_OPTIONS_KEY, $cached_options);
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
    return new Options("", wp_generate_uuid4(), [], "p/{{PostShortcode}}/{{PostSlug}}", "u/{{PersonShortcode}}/{{PersonSlug}}", "", true, false, false, false, true);
  }

  private static function get_resource_cpts()
  {
    $resources = ResourceRegistryService::get_all_instances();

    $cpts = array_map(fn ($resource) => $resource->cpt_name, $resources);

    return $cpts;
  }
}
