<?php

namespace Passle\PassleSync\Services;

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
        ?>
            <script>
                window.passleSyncSettings = {};
                window.passleSyncSettings['api_key'] = PASSLESYNC_API_KEY;
                window.passleSyncSettings['passle_shortcode'] = PASSLESYNC_SHORTCODE;
            </script>
            <div id="passle-sync-settings-root"></div>
        <?php
    }
}
