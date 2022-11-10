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
    if (!is_admin() && $query->is_home()) {
      static::modify_posts_query($query);
    }
  }

  public static function modify_tag_posts_query($query)
  {
    if (!is_admin() && $query->is_tag()) {
      static::modify_posts_query($query);
    }
  }

  public static function modify_posts_query($query)
  {
    $post_query = $query->get('post_type');
    if (is_array($post_query)) {
      if (in_array('post', $post_query)) {
        $query->set('post_type', array_push($post_query, PASSLESYNC_POST_TYPE));
      }
    } else {
      if ($post_query == 'post' || $post_query == '') {
        $query->set('post_type', array('post', PASSLESYNC_POST_TYPE));
      }
    }
  }
}
