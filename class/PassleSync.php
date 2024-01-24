<?php

namespace Passle\PassleSync;

use Passle\PassleSync\Services\CptRegistryService;
use Passle\PassleSync\Services\TaxonomyRegistryService;
use Passle\PassleSync\Services\EmbedService;
use Passle\PassleSync\Services\MenuService;
use Passle\PassleSync\Services\OptionsService;
use Passle\PassleSync\Services\RouteRegistryService;
use Passle\PassleSync\Services\SchedulerService;
use Passle\PassleSync\Services\ConfigService;
use Passle\PassleSync\Services\RewriteService;
use Passle\PassleSync\Services\TemplateService;
use Passle\PassleSync\Services\ThemeService;
use Passle\PassleSync\Services\UpgradeService;

use Passle\PassleSync\Services\Content\Passle\PassleTagGroupsContentService;

class PassleSync
{
  private const TAG_GROUPS_CACHE_CLEAN_EVENT_NAME = "passle_sync_clear_tag_groups_cache_event";

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
    UpgradeService::init();

    $options = OptionsService::get();

    if ($options->include_passle_tag_groups) {
        TaxonomyRegistryService::init();
        self::schedule_tag_groups_cache_cleanup();
    } else {
        self::unschedule_tag_groups_cache_cleanup();
    }
  }

  public static function activate()
  {
    flush_rewrite_rules();
  }

  public static function deactivate()
  {
    flush_rewrite_rules();
    self::unschedule_tag_groups_cache_cleanup();
  }

  /*
  *  The following code deals with scheduling tag groups cache cleanup, so when a new
  *  tag group is created in Passle, at some point within the hour, it is also created in WP
  *  following the init event. 
  *  If a Resource class is created for passle tag groups, this code will need to be moved probably.
  */ 
  public static function schedule_tag_groups_cache_cleanup() 
  {
    if (!wp_next_scheduled(self::TAG_GROUPS_CACHE_CLEAN_EVENT_NAME)) {
      wp_schedule_event(time(), "hourly", self::TAG_GROUPS_CACHE_CLEAN_EVENT_NAME);
    }
    add_action(self::TAG_GROUPS_CACHE_CLEAN_EVENT_NAME, [static::class, "tag_groups_cache_cleanup"]);
  }

  public static function tag_groups_cache_cleanup() 
  {
     PassleTagGroupsContentService::overwrite_cache(array());
  }

  public static function unschedule_tag_groups_cache_cleanup()
  {
    $timestamp = wp_next_scheduled(self::TAG_GROUPS_CACHE_CLEAN_EVENT_NAME);
    if ($timestamp) {
        wp_unschedule_event($timestamp, self::TAG_GROUPS_CACHE_CLEAN_EVENT_NAME);
    }
  }
}

