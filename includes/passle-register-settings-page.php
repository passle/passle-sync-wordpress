<?php

/*
 * Register the settings page for our integration.
 * The page (will) contains the API key and other config
 * for the integration.
 */

// Register the menu
add_action( "admin_menu", "passle_register_settings_menu" );
function passle_register_settings_menu() {
    add_submenu_page(   "options-general.php",                  // Which menu parent
                        "Passle Sync",                          // Page title
                        "Passle Sync",                          // Menu title
                        "manage_options",                       // Minimum capability (manage_options is an easy way to target Admins)
                        "passlesync",                           // Menu slug
                        "passle_register_settings_options"      // Callback that prints the markup
                    );
}

// Print the markup for the page
function passle_register_settings_options() {

    if ( !current_user_can( "manage_options" ) )  {
        wp_die( __( "You do not have sufficient permissions to access this page." ) );
    }

    if ( isset( $_GET['status'] ) && $_GET['status'] == 'success' ) {
    ?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php _e("Settings updated!", "passlesync"); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "passlesync" ); ?></span>
            </button>
        </div>
    <?php
    }

    ?>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="update_passle_sync_settings" />

            <h3><?php _e( "Passle Sync - Settings", "passlesync" ); ?></h3>
            <p>
                <label><?php _e( "API Key:", "passlesync" ); ?></label>
                <input class="" type="text" name="passle_api_key" value="<?php echo get_option( 'passle_api_key' ); ?>" />
            </p>

            <input class="button button-primary" type="submit" value="<?php _e( "Save", "passlesync" ); ?>" />
        </form>
    <?php

}


add_action( 'admin_post_update_passle_sync_settings', 'passle_sync_save_settings' );
function passle_sync_save_settings() {

    // Get the options that were sent
    $apiKey = ( !empty( $_POST["passle_api_key"] ) ) ? $_POST["passle_api_key"] : NULL;

    // Validation would go here

    // Update the values
    update_option( "passle_api_key", $apiKey, TRUE );

    // Redirect back to settings page
    // The ?page=passlesync corresponds to the "slug"
    // set in the fourth parameter of add_submenu_page() above.
    $redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=passlesync&status=success";
    header( "Location: " . $redirect_url );
    exit;

}
