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

    $options = htmlspecialchars(
      json_encode(OptionsService::get()),
      ENT_QUOTES,
      "UTF-8"
    );

?>
    <div id="passle-sync-settings-root" data-passlesync-options="<?php echo $options; ?>">
    </div>
<?php
  }
}
