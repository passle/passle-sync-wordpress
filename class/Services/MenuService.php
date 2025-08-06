<?php

namespace Passle\PassleSync\Services;

class MenuService
{
  public static function init()
  {
    add_action("admin_menu", [static::class, "register_menus"]);
  }

  public static function register_menus()
  {
    add_submenu_page(
      "options-general.php",
      "Passle Sync",
      "Passle Sync",
      "administrator",
      "passlesync",
      [static::class, "render_settings_menu"]
    );
  }

  public static function render_settings_menu()
  {

    $options = htmlspecialchars(
      json_encode(OptionsService::get()),
      ENT_QUOTES,
      "UTF-8"
    );

?>
    <div id="passle-sync-settings-root" data-passlesync-options="<?php echo esc_html($options); ?>" data-wp-nonce="<?php echo esc_html(wp_create_nonce("wp_rest")); ?>"></div>
<?php
  }
}
