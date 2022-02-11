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

  static function clamp(int $number, int $min, int $max)
  {
    return max($min, min($max, $number));
  }
}
