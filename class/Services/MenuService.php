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
            <div id="passle-sync-settings-root"
                data-api-key="<?php echo get_option(PASSLESYNC_PLUGIN_API_KEY) ?>"></div>
        <?php
    }
}
