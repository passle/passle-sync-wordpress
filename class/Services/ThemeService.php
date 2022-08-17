<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Models\PasslePost;

class ThemeService
{
  public static function init()
  {
    add_filter('the_author', [static::class, 'modified_the_author_name']);
    add_filter('get_the_author_display_name', [static::class, 'modified_the_author_name']);
    add_filter('author_link', [static::class, 'modified_author_link']);
    add_filter('get_avatar_url', [static::class, 'modified_get_avatar_url']);
    add_filter('the_content', [static::class, 'modified_the_content']);
    add_filter('get_post_metadata', [static::class, 'modified_post_layout'], 10, 5);
    add_filter('get_post_metadata', [static::class, 'modified_post_sidebar'], 10, 5);
  }

  public static function modified_the_author_name($display_name)
  {
    global $post;

    if (is_null($post)) return $display_name;
    if (get_post_type($post) != PASSLESYNC_POST_TYPE) return $display_name;

    $passle_post = new PasslePost($post);
    return $passle_post->primary_author->name;
  }

  public static function modified_author_link($link)
  {
    global $post;

    if (is_null($post)) return $link;
    if (get_post_type($post) != PASSLESYNC_POST_TYPE) return $link;

    $passle_post = new PasslePost($post);
    $link = $passle_post->primary_author->profile_url;
    return $link;
  }

  public static function modified_get_avatar_url($url)
  {
    global $post;

    if (is_null($post)) return $url;
    if (get_post_type($post) != PASSLESYNC_POST_TYPE) return $url;

    $passle_post = new PasslePost($post);
    $url = $passle_post->primary_author->avatar_url;
    return $url;
  }

  public static function modified_post_layout($value, $id, $key, $single, $meta_type)
  {
    if ($meta_type !== "post") return $value;

    global $post;

    if (is_null($post)) return $value;
    if (get_post_type($post) != PASSLESYNC_POST_TYPE) return $value;
    if ($post->ID !== $id) return $value;
    if ($key !== "_layout") return $value;

    return 'left';
  }

  public static function modified_post_sidebar($value, $id, $key, $single, $meta_type)
  {
    if ($meta_type !== "post") return $value;

    global $post;

    if (is_null($post)) return $value;
    if (get_post_type($post) != PASSLESYNC_POST_TYPE) return $value;
    if ($post->ID !== $id) return $value;
    if ($key !== "_sidebar") return $value;

    return 18;
  }

  public static function modified_the_content($content)
  {
    global $post;

    if (is_null($post)) return $content;
    if (get_post_type($post) != PASSLESYNC_POST_TYPE) return $content;

    $passle_post = new PasslePost($post);
    $quote = $passle_post->quote_text;
    $quote_url = $passle_post->quote_url;
    $featured_item = $passle_post->featured_item_html;

    $quote_html = "";
    if ($quote !== "") {
      $quote_html = '
      <blockquote class="wp-block-quote">
        <p>' . $quote . '</p>
        <cite>' . $quote_url . '</cite>
      </blockquote>';
    }

    $featured_item_styles = "";
    if ($featured_item !== "") {
      $featured_item_styles = '
        <style>
          .featured-item--video, .featured-item--audio, .featured-item--embed.legacy {
            width: 640px;
            padding-bottom: min(56.25%, 480px);
            position: relative;
          }
          .featured-item--video .psl-media-player-iframe-container,
          .featured-item--audio .psl-media-player-iframe-container,
          .featured-item--embed.legacy .psl-media-player-iframe-container {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            right: 0;
          }
          .featured-item--video .psl-media-player-iframe-container iframe,
          .featured-item--audio .psl-media-player-iframe-container iframe,
          .featured-item--embed.legacy .psl-media-player-iframe-container iframe{
            max-width: 640px;
            height: 100%;
            width: 100%;
          }
        </style>
      ';
    }

    return $content . $quote_html . $featured_item_styles . $featured_item;
  }
}
