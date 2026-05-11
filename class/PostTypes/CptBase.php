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

    // Split template into path and query string components — rewrite rules only match the path
    $template_parts = explode('?', $post_permalink_template, 2);
    $path_template = $template_parts[0];
    $query_string_template = isset($template_parts[1]) ? $template_parts[1] : '';

    // Escape special characters in the path only
    $regex = preg_quote($path_template, "/");

    if (strpos($path_template, '{{' . $template_variable . '}}') !== false) {
      // Shortcode is in the path — capture it and route directly via name
      $regex = preg_replace("/\\\\{\\\\{" . $template_variable . "\\\\}\\\\}/i", "([a-z0-9]+)", $regex);
      $regex = preg_replace("/\\\\{\\\\{[a-z0-9]+\\\\}\\\\}/i", "[a-z0-9\\-]+", $regex, -1);
      $regex = trim($regex, '/');
      $regex = '^' . $regex . '/?$';
      $query = "index.php?post_type={$resource->get_post_type()}&name=\$matches[1]";
    } else {
      // Shortcode is in the query string — match path as wildcard and resolve via request filter
      $regex = preg_replace("/\\\\{\\\\{[a-z0-9]+\\\\}\\\\}/i", "[a-z0-9\\-]+", $regex, -1);
      $regex = trim($regex, '/');
      $regex = '^' . $regex . '/?$';
      $query = "index.php?post_type={$resource->get_post_type()}";

      // Extract the query param name holding the shortcode (e.g. "postid" from "postid={{PostShortcode}}")
      if (preg_match('/([a-z0-9_]+)=\{\{' . $template_variable . '\}\}/i', $query_string_template, $param_matches)) {
        $shortcode_param = $param_matches[1];
        $post_type = $resource->get_post_type();

        // Register the param so WordPress doesn't strip it from the query string
        add_filter('query_vars', function ($vars) use ($shortcode_param) {
          $vars[] = $shortcode_param;
          return $vars;
        });

        // Translate e.g. postid=vp4yj4 → name=vp4yj4 (post_name is stored as the shortcode)
        add_filter('request', function ($query_vars) use ($shortcode_param, $post_type) {
          if (!empty($query_vars[$shortcode_param])) {
            $query_vars['post_type'] = $post_type;
            $query_vars['name'] = $query_vars[$shortcode_param];
            unset($query_vars[$shortcode_param]);
          }
          return $query_vars;
        });
      }
    }

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

    flush_rewrite_rules();
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
