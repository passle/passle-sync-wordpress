<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Models\Admin\Options;

class OptionsService
{
  public static function init()
  {
    add_option(PASSLESYNC_OPTIONS_KEY, static::get_default_options());
  }

  public static function get(): Options
  {
    $default_options = static::get_default_options();
    $saved_options = get_option(PASSLESYNC_OPTIONS_KEY);
    $saved_options_to_ignore = ["domain_ext", "site_url"];

    foreach ($saved_options as $key => $value) {
      if (in_array($key, $saved_options_to_ignore)) {
        continue;
      }
      $default_options->$key = $value;
    }

    return $default_options;
  }

  public static function set(Options $options)
  {
    update_option(PASSLESYNC_OPTIONS_KEY, $options);

    $cpts = static::get_resource_cpts();

    foreach ($cpts as $cpt) {
      $cpt::create_rewrite_rules();
    }

    RewriteService::add_preview_rewrite();
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
