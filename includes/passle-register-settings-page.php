<?php

/*
 * Register the settings page for our integration.
 * The page (will) contains the API key and other config
 * for the integration.
 */

// Register the menu
add_action("admin_menu", "passle_register_settings_menu");
function passle_register_settings_menu()
{
    add_submenu_page(
        "options-general.php",                  // Which menu parent
        "Passle Sync",                          // Page title
        "Passle Sync",                          // Menu title
        "manage_options",                       // Minimum capability (manage_options is an easy way to target Admins)
        "passlesync",                           // Menu slug
        "passle_register_settings_options"      // Callback that prints the markup
    );
}

// Print the markup for the page
function passle_register_settings_options()
{
    if (!current_user_can("manage_options")) {
        wp_die(__("You do not have sufficient permissions to access this page."));
    }

    if (isset($_GET['status']) && $_GET['status'] == 'success') {
?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php _e("Settings updated!", "passlesync"); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e("Dismiss this notice.", "passlesync"); ?></span>
            </button>
        </div>
    <?php
    }

    if (isset($_GET['sync']) && $_GET['sync'] == 'success') {
    ?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php _e("Sync succeeded!", "passlesync"); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e("Dismiss this notice.", "passlesync"); ?></span>
            </button>
        </div>
    <?php
    }

    if (isset($_GET['sync']) && $_GET['sync'] == 'failed') {
    ?>
        <div id="message" class="updated warning error is-dismissible">
            <p><?php _e("Sync failed!", "passlesync"); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e("Dismiss this notice.", "passlesync"); ?></span>
            </button>
        </div>
    <?php
    }

    if (isset($_GET['sync']) && $_GET['sync'] == 'inprogress') {
    ?>
        <div id="message" class="updated error is-dismissible">
            <p><?php _e("Sync still running!", "passlesync"); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e("Dismiss this notice.", "passlesync"); ?></span>
            </button>
        </div>
    <?php
    }

    if (isset($_GET['sync']) && $_GET['sync'] == 'started') {
    ?>
        <div id="message" class="updated notive is-dismissible">
            <p><?php _e("Sync started!", "passlesync"); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e("Dismiss this notice.", "passlesync"); ?></span>
            </button>
        </div>
    <?php
    }


    // TODO: Tidyup
    function get_shortcode($post)
    {
        return $post->post_shortcode;
    }

    // List all Passle Posts
    $posts = get_posts(array(
        'numberposts'   => -1,
        'post_type'     => array('PasslePost'),
    ));
    $existing_post_shortcodes = array_map('get_shortcode', $posts);

    $posts_to_add = array();
    $posts_to_delete = array();
    $unsynced_post_shortcodes = array();

    // TODO: Tidyup
    $posts_from_api = get_option('passle_posts');
    if (isset($posts_from_api['Posts']) && !empty($posts_from_api['Posts'])) {
        $unsynced_post_shortcodes = array_map(function ($p) {
            return $p['PostShortcode'];
        }, $posts_from_api['Posts']);

        $posts_to_add = array_filter($posts_from_api['Posts'], function ($p) use ($existing_post_shortcodes) {
            return !in_array($p['PostShortcode'], $existing_post_shortcodes);
        });

        $posts_to_delete = array_filter($posts, function ($p) use ($unsynced_post_shortcodes) {
            return !in_array($p->post_shortcode, $unsynced_post_shortcodes);
        });
    }

    $sync_in_progress = get_option('passle_sync_in_progress');



    ?>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="update_passle_sync_settings" />

        <h1><?php _e("Passle Sync - Settings", "passlesync"); ?></h1>
        <p>
            <label><?php _e("API Key:", "passlesync"); ?></label>
            <input class="" type="text" name="passle_api_key" value="<?php echo get_option('passle_api_key'); ?>" />
        </p>
        <p>
            <label><?php _e("Passle Shortcode:", "passlesync"); ?></label>
            <input class="" type="text" name="passle_shortcode" value="<?php echo get_option('passle_shortcode'); ?>" />
        </p>

        <input class="button button-primary" type="submit" value="<?php _e("Save", "passlesync"); ?>" />
    </form>
    <?php


    ?>
    <h2>Posts</h2>
    <?php

    if (count($posts) != 0) {
    ?>
        <ul class="posts">
            <?php foreach ($posts as $post) {
            ?>
                <li>
                    <a href="<?php echo get_edit_post_link($post); ?>" target="_blank">
                        <?php echo $post->post_type . ' - ' . $post->post_title . ' - ' . $post->post_shortcode; ?>
                    </a>
                </li>
            <?php
            } ?>
        </ul>
    <?php
    } else {
    ?>
        <ul class="coauthors">
            <li>No Passle Posts Yet</li>
        </ul>
    <?php
    }

    // Show unsynced posts and update the list from the API
    ?>
    <h2>Sync from API</h2>
    <?php

    if ($sync_in_progress) {
    ?>
        <p>Sync in progress...</p>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="check_sync_progress" />

            <input class="button button-primary" type="submit" value="<?php _e("Check progress", "passlesync"); ?>" />
        </form>
    <?php
    } else {
    ?>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="list_passle_posts_from_api" />

            <input class="button button-primary" type="submit" value="<?php _e("Fetch from API", "passlesync"); ?>" />
        </form>

        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="clear_unsynced_passle_posts" />

            <input class="button button-primary" type="submit" value="<?php _e("Clear list", "passlesync"); ?>" />
        </form>

        <?php /*
            if( count( $posts_to_add ) != 0 ) {
                ?>
                    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                        <input type="hidden" name="action" value="sync_all_unsynced_passle_posts" />

                        <input class="button button-primary" type="submit" value="<?php _e( "Sync all", "passlesync" ); ?>" />
                    </form>
                <?php
            }
        */ ?>

    <?php
    }


    ?>
    <ul>
        <?php foreach ($posts_to_add as $post) {
        ?>
            <li>

                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="sync_passle_post" />
                    <input type="hidden" name="title" value="<?php echo esc_attr($post['PostTitle']); ?>" />
                    <input type="hidden" name="content" value="<?php echo esc_attr($post['ContentTextSnippet']); ?>" />
                    <input type="hidden" name="date" value="<?php echo $post['PublishedDate']; ?>" />
                    <input type="hidden" name="shortcode" value="<?php echo $post['PostShortcode']; ?>" />
                    <input type="hidden" name="passle_shortcode" value="<?php echo $post['PassleShortcode']; ?>" />

                    <p>
                        <?php echo $post['PostTitle']; ?>
                    </p>

                    <input class="button button-primary" type="submit" value="<?php _e("Sync", "passlesync"); ?>" />
                </form>
            </li>
        <?php
        } ?>
    </ul>
<?php

}



add_action('wp_loaded', 'passle_sync_completed_status_redirect');
function passle_sync_completed_status_redirect()
{
    // For small requests, the request can have succeeded while the page was loading
    // So update the url param if that's the case
    $sync_in_progress = get_option('passle_sync_in_progress');

    if (isset($_GET['sync'])) {
        $sync_status_param = $_GET['sync'];

        if (!$sync_in_progress) {
            if (!empty($sync_status_param) && $sync_status_param !== 'success') {
                wp_redirect(get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=success");
                exit;
            }
        }
    }
}

add_action('admin_post_sync_passle_post', 'sync_passle_post');
function sync_passle_post()
{
    $title = (!empty($_POST["title"])) ? $_POST["title"] : NULL;
    $content = (!empty($_POST["content"])) ? $_POST["content"] : NULL;
    $date = (!empty($_POST["date"])) ? $_POST["date"] : NULL;
    $shortcode = (!empty($_POST["shortcode"])) ? $_POST["shortcode"] : NULL;
    $passle_shortcode = (!empty($_POST["passle_shortcode"])) ? $_POST["passle_shortcode"] : NULL;

    $new_post = array(
        'post_title'        => $title,
        'post_date'         => $date,
        'post_type'         => 'PasslePost',
        'post_content'      => $content,
        'post_status'       => 'publish',
        'comment_status'    => 'closed',
        'meta_input'    => array(
            'post_shortcode'    => $shortcode,
            'passle_shortcode'  => $passle_shortcode
        )
    );

    $pid = wp_insert_post($new_post, true);

    $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&id=" . $pid;
    header("Location: " . $redirect_url);
    exit;
}

add_action('admin_post_clear_unsynced_passle_posts', 'clear_unsynced_passle_posts');
function clear_unsynced_passle_posts()
{
    $data = array(
        'Posts' => array()
    );
    update_option("passle_posts", $data, false);

    $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync";
    header("Location: " . $redirect_url);
    exit;
}

add_action('admin_post_check_sync_progress', 'check_sync_progress');
function check_sync_progress()
{
    $sync_in_progress = get_option('passle_sync_in_progress');

    if ($sync_in_progress) {
        $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=inprogress";
        header("Location: " . $redirect_url);
        exit;
    } else {
        $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=success";
        header("Location: " . $redirect_url);
        exit;
    }
}

add_action('admin_post_update_passle_sync_settings', 'passle_sync_save_settings');
function passle_sync_save_settings()
{
    // Get the options that were sent
    $apiKey = (!empty($_POST["passle_api_key"])) ? $_POST["passle_api_key"] : NULL;
    $passleShortcode = (!empty($_POST["passle_shortcode"])) ? $_POST["passle_shortcode"] : NULL;

    // Validation would go here

    // Update the values
    update_option("passle_api_key", $apiKey, TRUE);
    update_option("passle_shortcode", $passleShortcode, TRUE);

    // Redirect back to settings page
    // The ?page=passlesync corresponds to the "slug"
    // set in the fourth parameter of add_submenu_page() above.
    $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&status=success";
    header("Location: " . $redirect_url);
    exit;
}

add_action('admin_post_list_passle_posts_from_api', 'list_passle_posts_from_api');
function list_passle_posts_from_api()
{
    $sync_in_progress = get_option('passle_sync_in_progress');

    if ($sync_in_progress) {
        $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=inprogress";
        header("Location: " . $redirect_url);
        exit;
    }

    $url = get_bloginfo("url") . "/wp-content/plugins/passle-sync/includes/passle-load-from-api.php";
    $request = wp_remote_get($url);

    if (is_wp_error($request)) {
        $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=failed";
        header("Location: " . $redirect_url);
        exit;
    } else {
        $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=passlesync&sync=started";
        header("Location: " . $redirect_url);
        exit;
    }
}
