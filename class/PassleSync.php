<?php

namespace Passle\PassleSync;

use Passle\PassleSync\Services\CptRegistryService;
use Passle\PassleSync\Services\EmbedService;
use Passle\PassleSync\Services\MenuService;
use Passle\PassleSync\Services\OptionsService;
use Passle\PassleSync\Services\RouteRegistryService;
use Passle\PassleSync\Services\SchedulerService;
use Passle\PassleSync\Services\ConfigService;
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

    register_activation_hook(__FILE__, [static::class, "activate"]);
    register_deactivation_hook(__FILE__, [static::class, "deactivate"]);

    // Hook into the 'init' action
    add_action('init', [static::class, 'wd_topics_taxonomy'], 0);
  }

  public static function activate()
  {
    flush_rewrite_rules();
  }

  public static function deactivate()
  {
    flush_rewrite_rules();
  }

  static function  wd_topics_taxonomy()
  {
    $labels = array(
      'name'                       => _x('Topics', 'Taxonomy General Name', 'hierarchical_tags'),
      'singular_name'              => _x('Topic', 'Taxonomy Singular Name', 'hierarchical_tags'),
      'menu_name'                  => __('Taxonomy', 'hierarchical_tags'),
      'all_items'                  => __('All Items', 'hierarchical_tags'),
      'parent_item'                => __('Parent Item', 'hierarchical_tags'),
      'parent_item_colon'          => __('Parent Item:', 'hierarchical_tags'),
      'new_item_name'              => __('New Item Name', 'hierarchical_tags'),
      'add_new_item'               => __('Add New Item', 'hierarchical_tags'),
      'edit_item'                  => __('Edit Item', 'hierarchical_tags'),
      'update_item'                => __('Update Item', 'hierarchical_tags'),
      'view_item'                  => __('View Item', 'hierarchical_tags'),
      'separate_items_with_commas' => __('Separate items with commas', 'hierarchical_tags'),
      'add_or_remove_items'        => __('Add or remove items', 'hierarchical_tags'),
      'choose_from_most_used'      => __('Choose from the most used', 'hierarchical_tags'),
      'popular_items'              => __('Popular Items', 'hierarchical_tags'),
      'search_items'               => __('Search Items', 'hierarchical_tags'),
      'not_found'                  => __('Not Found', 'hierarchical_tags'),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => true,
      "show_in_rest" => true
    );
    register_taxonomy('topics', array('post', 'passle-post'), $args);
  }
}
