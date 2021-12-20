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
        <!-- TODO: This is temporary, and should be handled by a React app. -->
        <div>Hello world</div>
        <button id="call-service">Call service</button>
        <script>
            document.querySelector("#call-service").onclick = async () => {
                const response = await fetch('http://wordpressdemo.test/wp-json/wp/v2/posts');
                const result = await response.text();
                console.log(result);
            }
        </script>
<?php
    }
}
