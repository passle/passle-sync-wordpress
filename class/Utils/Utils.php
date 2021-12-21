<?php

namespace Passle\PassleSync\Utils;

class Utils
{
    static function array_select(array $array, string $key)
    {
        $result = array_map(fn ($x) => $x[$key], $array);
        return $result;
    }

    static function array_select_multiple(array $array, string $key)
    {
        $result = array_merge(...array_map(fn ($x) => $x[$key], $array));
        return $result;
    }
}
