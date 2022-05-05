<?php

namespace Passle\PassleSync\Models;

use DateTime;
use Passle\PassleSync\Utils\Utils;

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
  public ?array $authors;
  public PassleAuthor $primary_author;
  /** @var PassleAuthor[] */
  public ?array $coauthors;
  /** @var PassleShareViewsNetwork[] */
  public ?array $share_views;
  /** @var PassleTweet[] */
  public ?array $tweets;
  public int $total_shares;
  public int $total_likes;
  public DateTime $date;
  public ?array $tags;
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

  /** Get the tags as a comma separated string. */
  public function get_joined_tags()
  {
    return implode(", ", $this->tags);
  }

  /*
   * Init methods
   */

  private function initialize()
  {
    $this->shortcode = $this->meta["post_shortcode"][0] ?? "";
    $this->passle_shortcode = $this->meta["passle_shortcode"][0] ?? "";
    $this->url = $this->meta["post_url"][0] ?? "";
    $this->slug = $this->meta["post_slug"][0] ?? "";
    $this->title = $this->wp_post->post_title ?? "";
    $this->content = $this->wp_post->post_content ?? "";
    $this->total_shares = $this->meta["post_total_shares"][0] ?? 0;
    $this->total_likes = $this->meta["post_total_likes"][0] ?? 0;
    $this->date = date_create($this->wp_post->post_date ?? "now");
    $this->tags = $this->meta["post_tags"] ?? [];
    $this->is_repost = $this->meta["post_is_repost"][0] ?? false;
    $this->estimated_read_time_seconds = $this->meta["post_estimated_read_time"][0] ?? 0;
    $this->estimated_read_time_minutes = max(ceil($this->estimated_read_time_seconds / 60), 1) ?? 0;
    $this->image_url = $this->meta["post_image_url"][0] ?? "";
    $this->featured_item_html = htmlspecialchars_decode($this->meta["post_featured_item_html"][0]) ?? "";
    $this->featured_item_media_type = $this->meta["post_featured_item_media_type"][0] ?? "";
    $this->featured_item_embed_type = $this->meta["post_featured_item_embed_type"][0] ?? "";
    $this->featured_item_embed_provider = $this->meta["post_featured_item_embed_provider"][0] ?? "";
    $this->excerpt = $this->wp_post->post_excerpt ?? "";
    $this->opens_in_new_tab = $this->meta["post_opens_in_new_tab"][0] ?? false;
    $this->quote_text = $this->meta["post_quote_text"][0] ?? "";
    $this->quote_url = $this->meta["post_quote_url"][0] ?? "";

    $this->initialize_authors();
    $this->initialize_share_views();
    $this->initialize_tweets();
  }

  private function initialize_authors()
  {
    // Fetch full author details for those that exist in the Wordpress database
    $wp_authors = get_posts([
      "post_type" => PASSLESYNC_AUTHOR_TYPE,
      "numberposts" => -1,
      "meta_query" => [
        "key" => "post_shortcode",
        "value" => $this->shortcode,
      ],
    ]);

    // Filter to authors and co-authors, fall back on post author data
    $this->authors = $this->map_authors("post_author_shortcodes", "post_authors", $wp_authors);
    $this->coauthors = $this->map_authors("post_coauthor_shortcodes", "post_coauthors", $wp_authors);

    $this->primary_author = $this->authors[0];
  }

  private function initialize_share_views()
  {
    $share_views = $this->meta["post_share_views"] ?? [];
    $this->share_views = $this->map_share_views($share_views);
  }

  private function initialize_tweets()
  {
    $tweets = $this->meta["post_tweets"] ?? [];
    $this->tweets = $this->map_tweets($tweets);
  }

  /*
   * Mapping methods
   */

  /** @param string[] $shortcodes */
  /** @param object[] $wp_authors */
  /** @param array[] $post_authors */
  private function map_authors(string $shortcode_meta_key, string $author_meta_key, array $wp_authors)
  {
    $post_authors = array_map(fn ($author) => unserialize($author), $this->meta[$author_meta_key] ?? []);

    // Filter to full authors contained in the list of $shortcodes, fall back on post author data
    $authors = array_map(function ($author_shortcode) use ($wp_authors, $post_authors) {
      $full_author = Utils::array_first($wp_authors, fn ($wp_author) => $wp_author->post_name === $author_shortcode);
      if (!empty($full_author)) return $full_author;

      $post_author = Utils::array_first($post_authors, fn ($post_author) => $post_author["shortcode"] === $author_shortcode);
      return $post_author;
    }, $this->meta[$shortcode_meta_key] ?? []);

    return array_map(fn ($author) => new PassleAuthor($author), $authors);
  }

  private function map_share_views(array $share_views)
  {
    return array_map(fn ($network) => new PassleShareViewsNetwork(unserialize($network)), $share_views);
  }

  private function map_tweets(array $tweets)
  {
    return array_map(fn ($tweet) => new PassleTweet(unserialize($tweet)), $tweets);
  }
}
