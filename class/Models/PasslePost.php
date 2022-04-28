<?php

namespace Passle\PassleSync\Models;

use DateTime;

/**
 * This class provides a simple interface for accessing properties of
 * Passle posts that have been saved to the Wordpress database.
 */
class PasslePost
{
  public string $shortcode;
  public string $passle_shortcode;
  public string $url;
  public string $slug;
  public string $title;
  public string $content;
  /** @var PassleAuthor[] */
  public array $authors;
  public PassleAuthor $primary_author;
  /** @var PassleAuthor[] */
  public array $coauthors;
  /** @var PassleShareViewsNetwork[] */
  public array $share_views;
  /** @var PassleTweet[] */
  public array $tweets;
  public int $total_shares;
  public int $total_likes;
  public DateTime $date;
  public array $tags;
  public bool $is_repost;
  public int $estimated_read_time_seconds;
  public int $estimated_read_time_minutes;
  public string $image_url;
  public string $featured_item_html;
  public string $featured_item_media_type;
  public string $featured_item_embed_type;
  public string $featured_item_embed_provider;
  public string $excerpt;
  public bool $opens_in_new_tab;
  public string $quote_text;
  public string $quote_url;

  private object $wp_post;
  private array $meta;

  public function __construct(object $wp_post)
  {
    $this->wp_post = $wp_post;
    $this->meta = get_post_meta($wp_post->ID);
    $this->initialize();
  }

  /**
   * Get the date in the specified format.
   * Format string should use (standard PHP formatting options)[https://www.php.net/manual/en/datetime.format.php].
   */
  public function get_date(string $format)
  {
    return date_format($this->date, $format);
  }

  /** Get the tags as a comma separated string */
  public function get_joined_tags()
  {
    return implode(", ", $this->tags);
  }

  /*
   * Init methods
   */

  private function initialize()
  {
    $this->shortcode = $this->meta["post_shortcode"][0];
    $this->passle_shortcode = $this->meta["passle_shortcode"][0];
    $this->url = $this->meta["post_url"][0];
    $this->slug = $this->meta["post_slug"][0];
    $this->title = $this->wp_post->post_title;
    $this->content = $this->wp_post->post_content;
    $this->total_shares = $this->meta["post_total_shares"][0];
    $this->total_likes = $this->meta["post_total_likes"][0];
    $this->date = date_create($this->wp_post->post_date);
    $this->tags = unserialize($this->meta["post_tags"][0]);
    $this->is_repost = $this->meta["post_is_repost"][0];
    $this->estimated_read_time_seconds = $this->meta["post_estimated_read_time"][0];
    $this->estimated_read_time_minutes = max(ceil($this->estimated_read_time_seconds / 60), 1);
    $this->image_url = $this->meta["post_image_url"][0];
    $this->featured_item_html = htmlspecialchars_decode($this->meta["post_featured_item_html"][0]);
    $this->featured_item_media_type = $this->meta["post_featured_item_media_type"][0];
    $this->featured_item_embed_type = $this->meta["post_featured_item_embed_type"][0];
    $this->featured_item_embed_provider = $this->meta["post_featured_item_embed_provider"][0];
    $this->excerpt = $this->wp_post->post_excerpt;
    $this->opens_in_new_tab = $this->meta["post_opens_in_new_tab"][0];
    $this->quote_text = $this->meta["post_quote_text"][0];
    $this->quote_url = $this->meta["post_quote_url"][0];

    $this->initialize_authors();
    $this->initialize_share_views();
    $this->initialize_tweets();
  }

  private function initialize_authors()
  {
    // Fetch all Passle and their metadata from the database
    global $wpdb;

    $query = $wpdb->prepare(
      "SELECT
        `post_name` as `shortcode`,
        `post_title` as `name`,
        `post_content` as `description`,
        `post_excerpt` as `role`,
        `meta_passle_shortcode`.`meta_value` as `passle_shortcode`,
        `meta_avatar_url`.`meta_value` as `avatar_url`,
        `meta_profile_url`.`meta_value` as `profile_url`
      FROM $wpdb->posts
      LEFT JOIN $wpdb->postmeta `meta_passle_shortcode` ON $wpdb->posts.`ID` = `meta_passle_shortcode`.`post_id`
      LEFT JOIN $wpdb->postmeta `meta_avatar_url` ON $wpdb->posts.`ID` = `meta_avatar_url`.`post_id`
      LEFT JOIN $wpdb->postmeta `meta_profile_url` ON $wpdb->posts.`ID` = `meta_profile_url`.`post_id`
      WHERE
        post_type = %s
        AND `meta_passle_shortcode`.`meta_key` = 'passle_shortcode'
        AND `meta_avatar_url`.`meta_key` = 'avatar_url'
        AND `meta_profile_url`.`meta_key` = 'profile_url'
      ",
      PASSLESYNC_AUTHOR_TYPE
    );

    $authors = $wpdb->get_results($query);

    // Filter to the authors of this post
    $post_author_shortcodes = array_map(fn ($author) => $author["shortcode"], unserialize($this->meta["post_authors"][0]));
    $post_coauthor_shortcodes = array_map(fn ($author) => $author["shortcode"], unserialize($this->meta["post_coauthors"][0]));

    $authors = array_values(array_filter($authors, fn ($author) => in_array($author->shortcode, $post_author_shortcodes)));
    $coauthors = array_values(array_filter($authors, fn ($author) => in_array($author->shortcode, $post_coauthor_shortcodes)));

    $this->authors = $this->map_authors($authors);
    $this->coauthors = $this->map_authors($coauthors);

    $this->primary_author = $this->authors[0];
  }

  private function initialize_share_views()
  {
    $share_views = unserialize($this->meta["post_share_views"][0]);
    $this->share_views = $this->map_share_views($share_views);
  }

  private function initialize_tweets()
  {
    $tweets = unserialize($this->meta["post_tweets"][0]);
    $this->tweets = $this->map_tweets($tweets);
  }

  /*
   * Mapping methods
   */

  private function map_authors(array $authors)
  {
    return array_map(fn ($author) => new PassleAuthor($author), $authors);
  }

  private function map_share_views(array $share_views)
  {
    return array_map(fn ($network) => new PassleShareViewsNetwork($network), $share_views);
  }

  private function map_tweets(array $tweets)
  {
    return array_map(fn ($tweet) => new PassleTweet($tweet), $tweets);
  }
}
