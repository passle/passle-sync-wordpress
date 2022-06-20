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
    return get_option(PASSLESYNC_OPTIONS_KEY) ?: static::get_default_options();
  }

  public static function set(Options $options)
  {
    update_option(PASSLESYNC_OPTIONS_KEY, $options);
    flush_rewrite_rules();
  }

  private static function get_default_options(): Options
  {
    return new Options("", wp_generate_uuid4(), [], "p", "u");
  }
}
