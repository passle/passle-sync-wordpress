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
      [$this, "render_settings_menu"]
    );
  }

  public function render_settings_menu()
  {
    $shortcodes = get_option(PASSLESYNC_SHORTCODE);
    $shortcodes_string = "";
    if (gettype($shortcodes) == "array") {
      $shortcodes_string = implode(",", $shortcodes);
    } else {
      $shortcodes_string = $shortcodes;
    }

?>
    <div id="passle-sync-settings-root" data-plugin-api-key="<?php echo get_option(PASSLESYNC_PLUGIN_API_KEY) ?>" data-client-api-key="<?php echo get_option(PASSLESYNC_CLIENT_API_KEY) ?>" data-passle-shortcodes="<?php echo $shortcodes_string ?>" data-post-permalink-prefix="<?php echo get_option(PASSLESYNC_POST_PERMALINK_PREFIX); ?>" data-person-permalink-prefix="<?php echo get_option(PASSLESYNC_PERSON_PERMALINK_PREFIX); ?>">
    </div>
<?php
  }
}
