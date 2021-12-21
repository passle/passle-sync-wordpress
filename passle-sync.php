<?php
/*
  Plugin Name: Passle Sync for WordPress
  Plugin URI: https://www.passle.net
  Description: Plugin to sync your Passle posts into your WordPress instance
  Version: 0.2
  Author: Passle
  Author URI: https://www.passle.net
  License: TBC
  Text Domain: passle
*/

defined('ABSPATH') || exit;

// Include passle functions - use require_once to stop the script if they're not found
$passle_base_path = plugin_dir_path(__FILE__);

define( 'PASSLE_SYNC_ASSET_MANIFEST', $passle_base_path . '/frontend/build/asset-manifest.json' );

require_once $passle_base_path . '/vendor/autoload.php';
require_once $passle_base_path . '/includes/passle-modify-home-query.php';
require_once $passle_base_path . '/includes/passle-register-settings-page.php';
require_once $passle_base_path . '/frontend/enqueue.php';
require_once $passle_base_path . '/constants.php';
require_once $passle_base_path . '/initialize.php';

// Autoload sync handlers
foreach (glob($passle_base_path . "/class/SyncHandlers/Handlers/*.php") as $filename) {
  require_once $filename;
}

update_option(PASSLESYNC_API_KEY, 'vp3a42-SPE9WT3-DDCTXGQ', true);
update_option(PASSLESYNC_SHORTCODE, 'vp3a43', true);
