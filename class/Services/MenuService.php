<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Services\Api\ApiServiceBase;

class MenuService
{
    public function register_menus()
    {
        add_submenu_page(
            "options-general.php",
            "Passle Sync",
            "Passle Sync",
            "manage_options",
            "passlesync",
            array($this, "render_settings_menu")
        );
    }

    public function render_settings_menu()
    {
        $shortcodes = get_option(PASSLESYNC_SHORTCODE);
        $shortcodes_string = "";
        if (gettype($shortcodes) == "array") {
            $shortcodes_string = implode(",", $shortcodes);
        }

        ?>
            <div id="passle-sync-settings-root"
                data-plugin-api-key="<?php echo get_option(PASSLESYNC_PLUGIN_API_KEY) ?>"
                data-client-api-key="<?php echo get_option(PASSLESYNC_CLIENT_API_KEY) ?>"
                data-passle-shortcodes="<?php echo $shortcodes_string ?>">
            </div>
        <?php
    }
}
