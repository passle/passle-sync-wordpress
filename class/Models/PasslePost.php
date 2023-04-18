<?php

namespace Passle\PassleSync\Models;

use DateTime;
use WP_Post;
use Passle\PassleSync\Utils\Utils;

/**
 * This class provides a simple interface for accessing properties of
 * Passle posts that have been saved to the Wordpress database.
 */
class PasslePost
{
  /** The shortcode for the post. */
  public string $shortcode;
  /** The shortcode for the Passle the post is published in. */
  public string $passle_shortcode;
  /** The URL for the post. */
  public string $url;
  /** The slug used in the post URL. */
  public string $slug;
  /** The title of the post. */
  public string $title;
  /** The post content as HTML. */
  public string $content;
  /**
   * A list containing the details of the primary authors of this post.
   * @var PassleAuthor[]|null
   */
  public ?array $authors;
  /** The primary author in the list of Passle authors. */
  public PassleAuthor $primary_author;
  /**
   * A list containing the details of the co-authors of this post.
   * @var PassleAuthor[]|null
   */
  public ?array $coauthors;
  /**
   * A list showing how often the post has been viewed via different social media channels.
   * @var PassleShareViewsNetwork[]|null
   */
  public ?array $share_views;
  /**
   * A list of tweets that have been chosen to be shown alongside this post.
   * @var PassleTweet[]
   */
  public ?array $tweets;
  /** An integer showing how many times this post has been shared. */
  public int $total_shares;
  /** An integer showing how many times the post has been liked. */
  public int $total_likes;
  /** A datetime value showing when this post was published. */
  public DateTime $date;
  /**
   * A list of tags for this post.
   * @var PassleTag[]|null
   */
  public ?array $tags;
  /** A boolean value showing whether this post is a repost of an original post. */
  public bool $is_repost;
  /** An integer showing the estimated time to read the post, in seconds. */
  public int $estimated_read_time_seconds;
  /** An integer showing the estimated time to read the post, in minutes. */
  public int $estimated_read_time_minutes;
  /** The URL for the post's featured media. */
  public string $image_url;
  /** The HTML content for the post's featured media. */
  public string $featured_item_html;
  /** An integer showing where the featured media is shown in the post. Values are: 0 - None; 1 - At the bottom of the post; 2 - At the top of the post; 3 - In the post’s header. */
  public int $featured_item_position;
  /** An integer showing what type of media the post's featured media is. 0 - None; 1 - Image; 2 - Video; 3 - Audio; 4 - Embedded link / item; 5 - Font; 6 - Document. */
  public int $featured_item_media_type;
  /** An integer showing what type of embed the post's embedded item is, if the featured media is of type '4 - Embedded link / item'. 0 - None; 1 - Photo; 2 - Video; 3 - Link; 4 - Rich. */
  public int $featured_item_embed_type;
  /** A string showing what provider the embedded item came from, if the featured media is of type '4 - Embedded link / item'. */
  public string $featured_item_embed_provider;
  /** The first few lines of the post. */
  public string $excerpt;
  /** A boolean value showing if the post should open in a new tab. */
  public bool $opens_in_new_tab;
  /** The text used in the post's quote. */
  public string $quote_text;
  /** The URL for the post's quote. */
  public string $quote_url;

  /* Options */
  private bool $load_authors;
  private bool $load_tags;

  private WP_Post $wp_post;
  private array $meta;

  /** 
   * Construct a new instance of the `PasslePost` class from the Wordpress post object.
   * 
   * @param WP_Post $wp_post The Wordpress post object.
   * @param array $options {
   *    Optional. Array containing options to be used when constructing the class.
   *    
   *    @type bool $load_authors Whether authors should be loaded. Default 'true'.
   * 
   *    @type bool $tags Whether tags  should be loaded. Default 'true'.
   * }
   * @return void
   */
  public function __construct(WP_Post $wp_post, array $options = [])
  {
    $options = wp_parse_args($options, [
      "load_authors" => true,
      "load_tags" => true,
    ]);

    $this->load_authors = $options["load_authors"];
    $this->load_tags = $options["load_tags"];

    $this->wp_post = $wp_post;
    $this->meta = get_post_meta($wp_post->ID);
    $this->initialize();
  }

  /**
   * Get the date in the specified format.
   * Format string should use [standard PHP formatting options](https://www.php.net/manual/en/datetime.format.php).
   * 
   * @param string $format The formatting options for the string.
   * @return DateTime
   */
  public function get_date(string $format)
  {
    return date_format($this->date, $format);
  }

  /**
   * Get an array containing the name of each tag.
   * 
   * @return string[]
   */
  public function get_tag_names()
  {
    return array_map(fn ($tag) => $tag->name, $this->tags);
  }

  /**
   * Get the tags as a comma separated string.
   * 
   * @return string
   */
  public function get_joined_tags()
  {
    return implode(", ", $this->get_tag_names());
  }

  /*
   * Init methods
   */

  /** @internal */
  private function initialize()
  {
    // Load post data
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
    $this->featured_item_position = $this->meta["post_featured_item_position"][0] ?? "";
    $this->featured_item_media_type = $this->meta["post_featured_item_media_type"][0] ?? "";
    $this->featured_item_embed_type = $this->meta["post_featured_item_embed_type"][0] ?? "";
    $this->featured_item_embed_provider = $this->meta["post_featured_item_embed_provider"][0] ?? "";
    $this->excerpt = $this->wp_post->post_excerpt ?? "";
    $this->opens_in_new_tab = $this->meta["post_opens_in_new_tab"][0] ?? false;
    $this->quote_text = $this->meta["post_quote_text"][0] ?? "";
    $this->quote_url = $this->meta["post_quote_url"][0] ?? "";

    if ($this->load_authors) {
      $this->initialize_authors();
    }

    if ($this->load_tags) {
      $this->initialize_tags();
    }

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

    if (count($this->authors) > 0) {
      $this->primary_author = $this->authors[0];
    } else {
      $default_author = new PassleAuthor(array(
        'name' => 'Deleted',
        'shortcode' => '',
        'profile_url' => '',
        'image_url' => PASSLESYNC_DEFAULT_PROFILE_IMAGE,
        'role' => ''
      ));
      $this->primary_author = $default_author;
    }
  }

  private function initialize_tags()
  {
    $wp_tags = get_the_tags() ?: [];
    $tags = $this->meta["post_tags"] ?? [];

    $this->tags = $this->map_tags($tags ?: [], $wp_tags ?: []);
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

  private function map_tags(array $tags, array $wp_tags)
  {
    return array_map(function ($tag) use ($wp_tags) {
      $matching_wp_tag = Utils::array_first($wp_tags, fn ($wp_tag) => $wp_tag->name === $tag) ?: null;
      return new PassleTag($tag, $matching_wp_tag);
    }, $tags);
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
