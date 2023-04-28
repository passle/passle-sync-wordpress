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
    $preview_permalink_prefix = OptionsService::get()->preview_permalink_prefix;

    $regex = '^' . $preview_permalink_prefix . '/([^/]*)/?';
    $query = 'index.php?passle_preview=$matches[1]';

    static::add_rewrite_rule($regex, $query);
    static::add_rewrite_tag('%passle_preview%');
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
