<?php

function passle_constants($constant_name, $value)
{
    $constant_name_prefix = 'PASSLESYNC_';
    $constant_name = $constant_name_prefix . $constant_name;
    if (!defined($constant_name))
        define($constant_name, $value);
}

passle_constants('REST_API_BASE', 'passlesync/v1');
passle_constants('API_KEY', 'passle_api_key');
passle_constants('SHORTCODE', 'passle_shortcode');
passle_constants('POST_TYPE', 'passle_post');
passle_constants('CLIENT_API_BASE', 'clientwebapi.passle.it/api');
