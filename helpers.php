<?php

if (!function_exists("dd")) {
  function dd(...$values)
  {
    var_dump($values);
    die();
  }
}

if (!function_exists("write_log")) {
  function write_log($log, $debug = true)
  {
    if (!$debug) return;

    if (is_array($log) || is_object($log)) {
      error_log(print_r($log, true));
    } else {
      error_log($log);
    }
  }
}
