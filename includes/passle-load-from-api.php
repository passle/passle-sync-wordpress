<?php
require_once '../../../../wp-config.php';

$sync_in_progress = get_option('passle_sync_in_progress');
if ($sync_in_progress) {
    echo 'Sync in progress';
    die('Sync in progress');
}

update_option("passle_sync_in_progress", true, TRUE);

$apiKey = get_option('passle_api_key');
$passleShortcode = get_option('passle_shortcode');
if (empty($apiKey) || empty($passleShortcode)) {
    $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=failed";
    header("Location: " . $redirect_url);
    exit;
}

$url = "http://clientwebapi.passle.it/api/posts?passleShortcode=" . $passleShortcode;
$request = wp_remote_get($url, array(
    'headers' => array(
        "apiKey" => $apiKey
    )
));

if (is_wp_error($request)) {
    $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=failed";
    header("Location: " . $redirect_url);
    exit;
}

$body = wp_remote_retrieve_body($request);
$data = json_decode($body, true);

if (!empty($data)) {
    update_option("passle_posts", $data, false);
}

update_option("passle_sync_in_progress", false, TRUE);
echo 'Sync complete';
