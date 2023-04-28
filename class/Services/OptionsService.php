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

    foreach ($saved_options as $key => $value) {
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

    flush_rewrite_rules();
  }

  private static function get_default_options(): Options
  {
    return new Options("", wp_generate_uuid4(), [], "p", "u", "", true, false, false, PASSLESYNC_DOMAIN_EXT, get_site_url());
  }

  private static function get_resource_cpts()
  {
    $resources = ResourceRegistryService::get_all_instances();

    $cpts = array_map(fn ($resource) => $resource->cpt_name, $resources);

    return $cpts;
  }
}
