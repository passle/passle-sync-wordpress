<?php
/*
  Plugin Name: Passle Sync
  Plugin URI: https://github.com/passle/passle-sync-wordpress
  Description: This plugin will sync your Passle posts and authors into your WordPress instance.
  Version: 2.0.0
  Author: Passle
  Author URI: https://www.passle.net
  License: MIT
  License URI: https://github.com/passle/passle-sync-wordpress/blob/master/LICENSE
  Text Domain: passle
*/

defined('ABSPATH') || exit;

// Include passle functions - use require_once to stop the script if they're not found
$passle_base_path = plugin_dir_path(__FILE__);

require_once $passle_base_path . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
require_once $passle_base_path . '/vendor/autoload.php';
require_once $passle_base_path . '/frontend/enqueue.php';
require_once $passle_base_path . '/constants.php';
require_once $passle_base_path . '/initialize.php';

// Autoload sync handlers
foreach (glob($passle_base_path . "/class/SyncHandlers/Handlers/*.php") as $filename) {
  require_once $filename;
}

require_once $passle_base_path . "/class/PassleSync.php";

function plugin_activation() 
{
  Passle\PassleSync\PassleSync::activate();
}

function plugin_deactivation() 
{
  Passle\PassleSync\PassleSync::deactivate();
}

register_activation_hook(__FILE__, "plugin_activation");
register_deactivation_hook(__FILE__, "plugin_deactivation");