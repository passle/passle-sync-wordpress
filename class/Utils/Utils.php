<?php

namespace Passle\PassleSync\Utils;

class Utils
{
  static function array_select(array $array, string $key)
  {
    // TODO: Check if $key is set 
    $result = array_map(fn ($x) => $x[$key], $array);
    return $result;
  }

  static function array_select_multiple(array $array, string $key)
  {
    // TODO: Check if $key is set
    if (is_null($array)) {
      return array();
    }
    $result = array_merge(...array_map(fn ($x) => $x[$key], $array));
    return $result;
  }

  static function array_first(array $array, callable $predicate)
  {
    foreach ($array as $value) {
      $matches = call_user_func($predicate, $value);
      if ($matches) {
        return $value;
      }
    }

    return false;
  }

  static function sformat($template, $params)
  {
    return str_replace(
      array_map(function ($v) {
        return "{" . $v . "}";
      }, array_keys($params)),
      $params,
      $template
    );
  }

  static function clamp(int $number, int $min, int $max)
  {
    return max($min, min($max, $number));
  }
  
  static function clear_featured_posts()
  {
    $args = array(
      'post_type'      => PASSLESYNC_POST_TYPE,
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'meta_query'     => array(
        'relation' => 'OR',
        array(
          'key'   => 'post_is_featured_on_passle_page',
          'compare' => 'EXISTS',
        ),
        array(
          'key'   => 'post_is_featured_on_post_page',
          'compare' => 'EXISTS',
        ),
      ),
    );

    $posts = get_posts($args);
 
    foreach ($posts as $post) {
      $post_meta = get_post_meta($post->ID, '', true);

      if (isset($post_meta["post_is_featured_on_passle_page"]) && $post_meta["post_is_featured_on_passle_page"]) {
        delete_post_meta($post->ID, "post_is_featured_on_passle_page");
      }

      if (isset($post_meta["post_is_featured_on_post_page"]) && $post_meta["post_is_featured_on_post_page"]) {
        delete_post_meta($post->ID, "post_is_featured_on_post_page");
      } 
    }
  }
}
