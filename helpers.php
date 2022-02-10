<?php

if (!function_exists("dd")) {
  function dd(...$values)
  {
    var_dump($values);
    die();
  }
}
