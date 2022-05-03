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
}
