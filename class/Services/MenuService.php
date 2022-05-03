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
      "administrator",
      "passlesync",
      [$this, "render_settings_menu"]
    );
  }

  public function render_settings_menu()
  {

    $options = htmlspecialchars(
      json_encode(OptionsService::get()),
      ENT_QUOTES,
      "UTF-8"
    );

?>
    <div id="passle-sync-settings-root" data-passlesync-options="<?php echo $options; ?>" data-wp-nonce="<?php echo wp_create_nonce("wp_rest"); ?>"></div>
<?php
  }
}
