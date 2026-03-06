<?php

namespace Passle\PassleSync\Models;

use DateTime;
use Passle\PassleSync\SyncHandlers\PostHandler;
use Passle\PassleSync\Utils\Utils;
use WP_Post;
use stdClass;

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
  /** A list of tag groups where post tags belong. */
  public ?array $tag_groups;
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
  /** A value showing where the featured media is shown in the post. Values are: 0 - None; 1 - At the bottom of the post; 2 - At the top of the post; 3 - In the post’s header. */
  public int|string $featured_item_position;
  /** An value showing what type of media the post's featured media is. 0 - None; 1 - Image; 2 - Video; 3 - Audio; 4 - Embedded link / item; 5 - Font; 6 - Document. */
  public int|string $featured_item_media_type;
  /** An value showing what type of embed the post's embedded item is, if the featured media is of type '4 - Embedded link / item'. 0 - None; 1 - Photo; 2 - Video; 3 - Link; 4 - Rich. */
  public int|string $featured_item_embed_type;
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
  /** The metadata title of the original post. */
  public string $metadata_title;
  /** The metadata description of the original post. */
  public string $metadata_description;

  /* Options */
  private bool $load_authors;
  private bool $load_tags;

  private WP_Post $wp_post;
  private array $meta;

  private array $passle_post;

  private array $wp_tag_lookup = [];
  private array $alias_cache = [];

  /** 
   * Construct a new instance of the `PasslePost` class from the Wordpress post object.
   * 
   * @param WP_Post|array|null $wp_post The WordPress post object.
   * @param array $options {
   *    Optional. Array containing options to be used when constructing the class.
   *    
   *    @type bool $load_authors Whether authors should be loaded. Default 'true'.
   * 
   *    @type bool $tags Whether tags  should be loaded. Default 'true'.
   * }
   * @return void
   */
  public function __construct(WP_Post|array|null $wp_post = null, array $options = array())
  {
    $options = wp_parse_args($options, [
      "load_authors" => true,
      "load_tags" => true,
    ]);

    if (is_object($wp_post)) {
     /**
      * Relevanssi (and other plugins) sometimes pass "raw" post objects.
      * These may not be fully-initialized WP_Post objects.
      * Instead, they may be plain stdClass objects or partial post representations with $post->filter = 'raw'.
      * Trying to treat them like full WP_Post objects may result in fatal errors.
      * Hence this check and wrapping with WP_Post if needed.
      */
      if ($wp_post instanceof WP_Post) {
        $this->wp_post = $wp_post;
      } elseif ($wp_post instanceof stdClass && ($wp_post->filter ?? '') === "raw") {
        $this->wp_post = new WP_Post($wp_post);
      } else {
        return;
      }

      $this->meta = get_post_meta($wp_post->ID);
      $this->initialize_wp_post();
    } elseif (is_array($wp_post)) {
      $this->passle_post = $wp_post;
      $this->initialize_passle_post();
    } else {
      return;
    }

    $this->load_authors = $options["load_authors"];
    $this->load_tags = $options["load_tags"];

    if ($this->load_authors) {
      $this->initialize_authors();
    }

    if ($this->load_tags) {
      // Load only post tag names from Wordpress via the default post_tag taxonomy
      // instead of loading all wp tags here
      $post_tag_names = isset($this->meta)
        ? ($this->meta["post_tags"] ?? [])
        : ($this->passle_post["Tags"] ?? []);

      $all_wp_tags = get_terms([
        'taxonomy' => 'post_tag',
        'name__in' => $post_tag_names,
        'hide_empty' => false,
        'update_term_meta_cache' => false,
      ]);
      $this->initialize_tags($all_wp_tags);
      $this->initialize_tag_groups($all_wp_tags);
    }

    $this->initialize_share_views();
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
  private function initialize_wp_post()
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
    $this->tag_groups = $this->meta["post_tag_groups"] ?? [];
    $this->is_repost = $this->meta["post_is_repost"][0] ?? false;
    $this->estimated_read_time_seconds = $this->meta["post_estimated_read_time"][0] ?? 0;
    $this->estimated_read_time_minutes = max(ceil($this->estimated_read_time_seconds / 60), 1) ?? 0;
    $this->image_url = $this->meta["post_image_url"][0] ?? "";
    $this->featured_item_html = htmlspecialchars_decode($this->meta["post_featured_item_html"][0] ?? "", ENT_QUOTES) ?? "";
    $this->featured_item_position = $this->meta["post_featured_item_position"][0] ?? 0;
    $this->featured_item_media_type = $this->meta["post_featured_item_media_type"][0] ?? 0;
    $this->featured_item_embed_type = $this->meta["post_featured_item_embed_type"][0] ?? 0;
    $this->featured_item_embed_provider = $this->meta["post_featured_item_embed_provider"][0] ?? "";
    $this->excerpt = $this->wp_post->post_excerpt ?? "";
    $this->opens_in_new_tab = $this->meta["post_opens_in_new_tab"][0] ?? false;
    $this->quote_text = $this->meta["post_quote_text"][0] ?? "";
    $this->quote_url = $this->meta["post_quote_url"][0] ?? "";
    $this->metadata_title = $this->meta["post_metadata_title"][0] ?? "";
    $this->metadata_description = $this->meta["post_metadata_description"][0] ?? "";
  }

  /** @internal */
  private function initialize_passle_post()
  {
    $this->shortcode = $this->passle_post["PostShortcode"] ?? "";
    $this->passle_shortcode = $this->passle_post["PassleShortcode"] ?? "";
    $this->url = $this->passle_post["PostUrl"] ?? "";
    $this->slug = $this->passle_post["PostSlug"] ?? "";
    $this->title = $this->passle_post["PostTitle"] ?? "";
    $this->content = $this->passle_post["PostContentHtml"] ?? "";
    $this->total_shares = $this->passle_post["TotalShares"] ?? 0;
    $this->total_likes = $this->passle_post["TotalLikes"] ?? 0;
    $this->date = $this->initialize_passle_date($this->passle_post["PublishedDate"] ?? "now");
    $this->tags = $this->passle_post["Tags"] ?? [];
    $this->tag_groups = $this->passle_post["TagGroups"] ?? [];
    $this->is_repost = $this->passle_post["IsRepost"] ?? false;
    $this->estimated_read_time_seconds = $this->passle_post["EstimatedReadTimeSeconds"] ?? 0;
    $this->estimated_read_time_minutes = max(ceil($this->estimated_read_time_seconds / 60), 1) ?? 0;
    $this->image_url = $this->passle_post["ImageUrl"] ?? "";
    $this->featured_item_html = htmlspecialchars_decode($this->passle_post["FeaturedItemHtml"] ?? "");
    $this->featured_item_position = $this->passle_post["FeaturedItemPosition"] ?? 0;
    $this->featured_item_media_type = $this->passle_post["FeaturedItemMediaType"] ?? 0;
    $this->featured_item_embed_type = $this->passle_post["FeaturedItemEmbedType"] ?? 0;
    $this->featured_item_embed_provider = $this->passle_post["FeaturedItemEmbedProvider"] ?? "";
    $this->excerpt = $this->passle_post["ContentTextSnippet"] ?? "";
    $this->opens_in_new_tab = $this->passle_post["OpensInNewTab"] ?? false;
    $this->quote_text = $this->passle_post["QuoteText"] ?? "";
    $this->quote_url = $this->passle_post["QuoteUrl"] ?? "";
    $this->metadata_title = $this->passle_post["MetaData"]['Title'] ?? "";
    $this->metadata_description = $this->passle_post["MetaData"]['Description'] ?? "";
  }

  /** @internal */
  private function initialize_passle_date(string $date)
  {
    $result = date_create($date);
    $year = date_format($result, "Y");

    // Preview posts don't have a published date, so we'll use the current date
    if ($year <= 1970) {
      return date_create("now");
    }

    return date_create($date);
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
    $this->authors = $this->map_authors("post_author_shortcodes", "post_authors", "Authors", $wp_authors);
    $this->coauthors = $this->map_authors("post_coauthor_shortcodes", "post_coauthors", "CoAuthors", $wp_authors);

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

  private function initialize_tags(array $wp_tags)
  {   
    if (isset($this->meta["post_tags"])) {
      $tags = $this->meta["post_tags"] ?? [];
    } else {
      $tags = $this->passle_post["Tags"] ?? [];
    }

    if (empty($tags)) {
      $this->tags = [];
      return;
    }

    foreach ($wp_tags as $wp_tag) {
      $this->wp_tag_lookup[html_entity_decode($wp_tag->name)] = $wp_tag;
    }

    // Map tags in batches. This will only make a difference to posts with more than 50 tags
    $batch_size = 50;
    $tags_chunks = array_chunk($tags, $batch_size);

    $mapped_tags = [];
    foreach ($tags_chunks as $chunk) {
      $chunk_wp_tags = array_filter($wp_tags, fn($t) => in_array(html_entity_decode($t->name), $chunk, true));
      $mapped_tags = array_merge($mapped_tags, $this->map_tags($chunk, $chunk_wp_tags));
    }

    $this->tags = $mapped_tags;
  }

  private function initialize_tag_groups(array $wp_tags)
  {
    if (isset($this->meta["post_tag_groups"])) {
      $tag_groups = $this->meta["post_tag_groups"] ?? [];
    } else {
      $tag_groups = $this->passle_post["TagGroups"] ?? [];
    }

    $this->tag_groups = [];

    foreach ((array) $tag_groups as $tag_group) {
      $unserialized_tag_group = maybe_unserialize($tag_group);
      $unserialized_tag_group = is_array($unserialized_tag_group) ? $unserialized_tag_group : [];

      $group_tags = $unserialized_tag_group["Tags"] ?? [];

      // Batch tags for this group
      $batch_size = 20;
      $tags_chunks = array_chunk($group_tags, $batch_size);

      $mapped_group_tags = [];
      foreach ($tags_chunks as $chunk) {
        $chunk_wp_tags = array_filter($wp_tags, fn($t) => in_array(html_entity_decode($t->name), $chunk, true));
        $mapped_group_tags = array_merge($mapped_group_tags, $this->map_tags($chunk, $chunk_wp_tags));
      }

      $this->tag_groups[] = [
        "name" => $unserialized_tag_group["Name"] ?? "",
        "tags" => $mapped_group_tags
      ];

      unset($unserialized_tag_group); // Free memory
    }
  }

  private function initialize_share_views()
  {
    if (isset($this->meta)) {
      $share_views = isset($this->meta["post_share_views"]) ? $this->meta["post_share_views"] : array();
    } else {
      $share_views = $this->passle_post["ShareViews"] ?? array();
    }

    $this->share_views = $this->map_share_views($share_views ?? array());
  }

  /*
   * Mapping methods
   */

  private function map_authors(string $shortcode_meta_key, string $author_meta_key, string $author_post_key, array $wp_authors)
  {
    if (isset($this->meta)) {
      $post_authors = array_map(fn ($author) => unserialize($author), $this->meta[$author_meta_key] ?? []);
      if (isset($this->meta[$shortcode_meta_key])) {
        $author_shortcodes = $this->meta[$shortcode_meta_key];
      } else {
        $author_shortcodes = Utils::array_select($post_authors, "shortcode");
      }
    } else {
      $post_authors = PostHandler::map_authors($this->passle_post[$author_post_key]);
      $author_shortcodes = Utils::array_select($post_authors, "shortcode");
    }

    // Filter to full authors contained in the list of $shortcodes, fall back on post author data
    $authors = array_map(function ($author_shortcode) use ($wp_authors, $post_authors) {
      $full_author = Utils::array_first($wp_authors, fn ($wp_author) => $wp_author->post_name === $author_shortcode);
      if (!empty($full_author)) return $full_author;

      $post_author = Utils::array_first($post_authors, fn ($post_author) => $post_author["shortcode"] === $author_shortcode);
      return $post_author;
    }, $author_shortcodes ?? array());

    return array_map(fn ($author) => new PassleAuthor($author), $authors);
  }

  private function map_tags(array $tags) {

    if (empty($tags)) {
      return [];
    }

    $term_ids_to_query = [];

    foreach ($tags as $tag) {

      $decoded_tag = html_entity_decode($tag);

      if (!isset($this->wp_tag_lookup[$decoded_tag])) {
        continue;
      }

      $term_id = $this->wp_tag_lookup[$decoded_tag]->term_id;

      if (!array_key_exists($term_id, $this->alias_cache)) {
        $term_ids_to_query[] = $term_id;
      }
    }

    $term_ids_to_query = array_unique($term_ids_to_query);

    if (!empty($term_ids_to_query)) {

        // WordPress bulk loads term meta
        update_termmeta_cache($term_ids_to_query);

        foreach ($term_ids_to_query as $term_id) {
          $this->alias_cache[$term_id] = get_term_meta($term_id, 'aliases', false);
        }
    }

    $result = [];

    foreach ($tags as $tag) {

        $decoded_tag = html_entity_decode($tag);
        $wp_tag = $this->wp_tag_lookup[$decoded_tag] ?? null;

        $aliases = [];

        if ($wp_tag) {
          $aliases = $this->alias_cache[$wp_tag->term_id] ?? [];
        }

        $result[] = new PassleTag($tag, $wp_tag, $aliases);
    }

    return $result;
  }

  private function map_share_views(array $share_views)
  {
    if (isset($this->meta)) {
      return array_map(fn ($network) => new PassleShareViewsNetwork(unserialize($network)), $share_views);
    } else {
      return array_map(fn ($network) => new PassleShareViewsNetwork($network), PostHandler::map_share_views($share_views));
    }
  }
}
