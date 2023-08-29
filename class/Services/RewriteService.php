<?php

namespace Passle\PassleSync\Services;

class RewriteService
{
  public static function init()
  {
    add_action("init", [static::class, "add_preview_rewrite"]);
  }

  public static function add_preview_rewrite()
  {
    $template_variable = "PostShortcode";
    $preview_permalink_template = OptionsService::get()->preview_permalink_template;

    if(!$preview_permalink_template) return;

    // Escape special characters in the path
    $regex = preg_quote($preview_permalink_template);

    // Replace the template variable with a capture group to extract the shortcode
    // e.g. if we're trying to extract the post shortcode, we want to replace {{PostShortcode}} with (?<shortcode>[a-z0-9]+)
    $regex = preg_replace("/\\\\{\\\\{" . $template_variable . "\\\\}\\\\}/i", "([a-z0-9]+)", $regex);

    // Replace the remaining template variables with wildcards
    // e.g. {{PassleShortcode}} will be replaced with [a-z0-9\-]+
    $regex = preg_replace("/\\\\{\\\\{[a-z0-9]+\\\\}\\\\}/i", "[a-z0-9\\-]+", $regex, -1, $count);

    $query = "index.php?passle_preview=\$matches[1]";

    static::add_rewrite_rule($regex, $query);
    static::add_rewrite_tag('%passle_preview%');

    flush_rewrite_rules();
  }

  private static function add_rewrite_rule(string $regex, string $query)
  {
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

  private static function add_rewrite_tag(string $tag)
  {
    add_rewrite_tag($tag, '([^&]+)');
  }
}
