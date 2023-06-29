<?php

namespace Passle\PassleSync\PostTypes;

use Passle\PassleSync\Utils\ResourceClassBase;

abstract class CptBase extends ResourceClassBase
{
  protected abstract static function get_cpt_args(): array;
  protected abstract static function get_permalink_template(): string;

  public static function init()
  {
    add_action("init", [static::class, "create_post_type"]);
    add_action("init", [static::class, "create_rewrite_rules"]);
    add_filter("post_type_link", [static::class, "rewrite_post_permalink"], 1, 2);
  }

  public static function create_post_type()
  {
    $resource = static::get_resource_instance();

    $name_singular = "Passle " . ucfirst($resource->display_name_singular);
    $name_plural = "Passle " . ucfirst($resource->display_name_plural);

    $labels = [
      "name" => $name_plural,
      "singular_name" => $name_singular,
      "menu_name" => $name_plural,
      "name_admin_bar" => $name_singular,
      "add_new" => "Add New",
      "add_new_item" => "Add New $name_singular",
      "new_item" => "New $name_singular",
      "edit_item" => "Edit $name_singular",
      "view_item" => "View $name_singular",
      "all_items" => "All $name_plural",
      "search_items" => "Search $name_plural",
      "parent_item_colon" => "Parent $name_singular",
      "not_found" => "No $name_plural Found",
      "not_found_in_trash" => "No $name_plural Found in Trash"
    ];

    $args = array_merge_recursive([
      "labels" => $labels,
      "public" => true,
      "exclude_from_search" => false,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_nav_menus" => true,
      "show_in_menu" => true,
      "show_in_admin_bar" => false,
      "map_meta_cap" => false,
      "menu_position" => 5,
      "capability_type" => "post",
      "capabilities" => [
        "create_posts" => "do_not_allow",
      ],
      "hierarchical" => false,
      "supports" => ["title", "custom-fields"],
      "taxonomies" => ["post_tag"],
      "has_archive" => true,
      "rewrite" => false,
      "query_var" => true,
      "show_in_rest" => true
    ], static::get_cpt_args());

    register_post_type($resource->get_post_type(), $args);
  }

  public static function create_rewrite_rules()
  {
    $resource = static::get_resource_instance();

    $template_variable = $resource->get_permalink_template_variable();
    $post_permalink_template = static::get_permalink_template();

    // Escape special characters in the path
    $regex = preg_quote($post_permalink_template);

    // Replace the template variable with a capture group to extract the shortcode
    // e.g. if we're trying to extract the post shortcode, we want to replace {{PostShortcode}} with (?<shortcode>[a-z0-9]+)
    $regex = preg_replace("/\\\\{\\\\{" . $template_variable . "\\\\}\\\\}/i", "([a-z0-9]+)", $regex);

    // Replace the remaining template variables with wildcards
    // e.g. {{PassleShortcode}} will be replaced with [a-z0-9\-]+
    $regex = preg_replace("/\\\\{\\\\{[a-z0-9]+\\\\}\\\\}/i", "[a-z0-9\\-]+", $regex, -1, $count);

    $query = "index.php?post_type={$resource->get_post_type()}&name=\$matches[1]";

    // Remove existing rewrite rules that match query
    global $wp_rewrite;
    foreach ($wp_rewrite->extra_rules_top as $ruleRegex => $ruleQuery) {
      if (false !== strpos($ruleQuery, $query)) {
        unset($wp_rewrite->extra_rules_top[$ruleRegex]);
      }
    }

    // Add new rewrite rule
    add_rewrite_rule(
      $regex,
      $query,
      'top'
    );
  }

  public static function rewrite_post_permalink($permalink, $post)
  {
    $resource = static::get_resource_instance();

    if ($post->post_type !== $resource->get_post_type()) return $permalink;

    return static::rewrite_permalink($resource, $post);
  }

  public static function rewrite_permalink($resource, $post)
  {
    $template_variables = [
      "{{PassleShortcode}}" => "example",
    ];

    $post_shortcode = get_post_meta($post->ID, $resource->get_meta_shortcode_name(), true);
    $post_slug = get_post_meta($post->ID, $resource->get_meta_slug_name(), true);

    switch ($resource->name_singular) {
      case "post":
        $template_variables["{{PostShortcode}}"] = $post_shortcode;
        $template_variables["{{PostSlug}}"] = $post_slug;
        break;
      case "person":
        $template_variables["{{PersonShortcode}}"] = $post_shortcode;
        $template_variables["{{PersonSlug}}"] = $post_slug;
        break;
    }

    $path = static::get_permalink_template();
    foreach ($template_variables as $key => $value) {
      $path = str_replace($key, $value, $path);
    }

    return home_url($path);
  }
}
