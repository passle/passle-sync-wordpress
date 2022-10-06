<?php

namespace Passle\PassleSync\Services;

class ConfigService
{
  public static function init()
  {
    $opts = OptionsService::get();
    if (isset($opts->include_passle_posts_on_home_page) && $opts->include_passle_posts_on_home_page) {
      add_action("pre_get_posts", [static::class, "modify_home_posts_query"]);
    }

    if (isset($opts->include_passle_posts_on_tag_page) && $opts->include_passle_posts_on_tag_page) {
      add_action("pre_get_posts", [static::class, "modify_tag_posts_query"]);
    }
  }

  public static function modify_home_posts_query($query)
  {
    if (!is_admin() && $query->is_home() && $query->is_main_query()) {
      if ($query->get('post_type') == 'post' || $query->get('post_type') == '') {
        $query->set('post_type', array('post', PASSLESYNC_POST_TYPE));
      }
    }
  }

  public static function modify_tag_posts_query($query)
  {
    if (!is_admin() && $query->is_tag() && $query->is_main_query()) {
      if ($query->get('post_type') == 'post' || $query->get('post_type') == '') {
        $query->set('post_type', array('post', PASSLESYNC_POST_TYPE));
      }
    }
  }
}
