<?php

namespace Passle\PassleSync;

use Passle\PassleSync\Services\CptRegistryService;
use Passle\PassleSync\Services\EmbedService;
use Passle\PassleSync\Services\MenuService;
use Passle\PassleSync\Services\OptionsService;
use Passle\PassleSync\Services\RouteRegistryService;
use Passle\PassleSync\Services\SchedulerService;
use Passle\PassleSync\Services\ConfigService;
use Passle\PassleSync\Services\RewriteService;
use Passle\PassleSync\Services\TemplateService;
use Passle\PassleSync\Services\ThemeService;

class PassleSync
{
  public static function initialize()
  {
    MenuService::init();
    RouteRegistryService::init();
    CptRegistryService::init();
    EmbedService::init();
    OptionsService::init();
    SchedulerService::init();
    ConfigService::init();
    ThemeService::init();
    RewriteService::init();
    TemplateService::init();

    register_activation_hook(__FILE__, [static::class, "activate"]);
    register_deactivation_hook(__FILE__, [static::class, "deactivate"]);
  }

  public static function activate()
  {
    flush_rewrite_rules();
  }

  public static function deactivate()
  {
    flush_rewrite_rules();
  }
}
