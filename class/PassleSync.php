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
use Passle\PassleSync\Services\ResourceRegistryService;

use Passle\PassleSync\Services\Content\Passle\PassleTagGroupsContentService;
use Passle\PassleSync\Services\Content\Passle\PasslePostsContentService;
use Passle\PassleSync\Services\Content\Passle\PasslePeopleContentService;

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

    /*
    *  The following block will help with testing the plugin in various environments.
    *  It's pointless & confusing to maintain some options in cache between staging/live builds.
    */
    if ($options->domain_ext != PASSLESYNC_DOMAIN_EXT) {
        $options->passle_shortcodes = [];
        $options->passle_api_key = "";
        $options->include_passle_tag_groups = false;
        OptionsService::set($options, false);
        self::clear_all_caches();
    }

    if ($options->include_passle_tag_groups) {
        TaxonomyRegistryService::init();
        self::schedule_tag_groups_cache_cleanup();
    } else {
        self::unschedule_tag_groups_cache_cleanup();
        self::clear_tag_groups_cache();
    }
  }

  public static function activate()
  {
    flush_rewrite_rules();
  }

  public static function deactivate()
  {
    flush_rewrite_rules();
    self::reset_entities_marked_for_deletion();
    self::reset_entities_last_synced_page();
    self::clear_all_caches();
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
    static::clear_tag_groups_cache();
  }

  public static function unschedule_tag_groups_cache_cleanup()
  {
    $timestamp = wp_next_scheduled(self::TAG_GROUPS_CACHE_CLEAN_EVENT_NAME);
    if ($timestamp) {
      wp_unschedule_event($timestamp, self::TAG_GROUPS_CACHE_CLEAN_EVENT_NAME);
    }
  }

  public static function reset_entities_marked_for_deletion() 
  {
    $wp_entities_to_delete = get_posts([
      'meta_key'   => '_pending_deletion',
      'meta_value' => true,
      'posts_per_page' => -1, 
    ]);

    foreach ($wp_entities as $entity) {
      delete_post_meta($entity->ID, '_pending_deletion');
    }
  }

  public static function reset_entities_last_synced_page()
  {
    $resources = ResourceRegistryService::get_all_instances();
      
    foreach($resources as $resource) {
      update_option($resource->last_synced_page_option_name, 1);
    }
  }

  private static function clear_all_caches()
  {
    self::clear_tag_groups_cache();
    self::clear_people_cache();
  }

  private static function clear_tag_groups_cache() 
  { 
    PassleTagGroupsContentService::overwrite_cache(array());
    // PasslePostsContentService::overwrite_cache needs to happen so next time posts sync their tag mappings are updated
    PasslePostsContentService::overwrite_cache(array());
  }

  private static function clear_people_cache() 
  {
    PasslePeopleContentService::overwrite_cache(array());
  }
}

