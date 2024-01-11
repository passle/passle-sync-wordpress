<?php

function passle_constants($constant_name, $value)
{
    $constant_name_prefix = "PASSLESYNC_";
    $constant_name = $constant_name_prefix . $constant_name;
    if (!defined($constant_name))
        define($constant_name, $value);
}

/* !! DO NOT EDIT THIS LINE. IT WILL BE AUTOMATICALLY REPLACED BY GULP. !! */
passle_constants("DOMAIN_EXT", "localhost");

passle_constants("REMOTE_HOSTING_TYPE", 2); // WordPress
passle_constants("REST_API_BASE", "passlesync/v1");
passle_constants("OPTIONS_KEY", "passlesync_options");
passle_constants("POSTS_CACHE", "passlesync_posts_cache");
passle_constants("AUTHORS_CACHE", "passlesync_authors_cache");
passle_constants("POST_TYPE", "passle-post");
passle_constants("AUTHOR_TYPE", "passle-author");
passle_constants("TAG_GROUP_TAXONOMY", "tag_group");
passle_constants("CLIENT_API_BASE", "clientwebapi.passle." . PASSLESYNC_DOMAIN_EXT . "/api/v2");
passle_constants("DEFAULT_AVATAR_URL", "https://images.passle.net/200x200/assets/images/no_avatar.png");
passle_constants("ASSET_MANIFEST", plugin_dir_path(__FILE__) . "/frontend/dist/asset-manifest.json");
passle_constants("DEFAULT_PROFILE_IMAGE", "http://s3.amazonaws.com/passle-public/fb_passle_app_250.png");
