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

defined( 'ABSPATH' ) or die( 'No direct access, please' );

// Include passle functions - use require_once to stop the script if they're not found
require_once plugin_dir_path(__FILE__) . 'includes/passle-custom-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/passle-modify-home-query.php';
require_once plugin_dir_path(__FILE__) . 'includes/passle-register-settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/passle-rest-api.php';
