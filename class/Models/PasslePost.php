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
  private array $meta = [];
  private array $passle_post = [];

  private array $wp_tag_lookup = [];

  /** @var array<int, array> */
  private static array $alias_cache = [];

  /** @var array<string, array> */
  private static array $author_cache = [];

  /** @var array<string, array> */
  private static array $term_lookup_cache = [];

  private const MAX_TAG_LOOKUP_COUNT = 100;

  /**
   * Construct a new instance of the `PasslePost` class from the Wordpress post object.
   *
   * @param WP_Post|array|null $wp_post The WordPress post object.
   * @param array $options {
   *    Optional. Array containing options to be used when constructing the class.
   *
   *    @type bool $load_authors Whether authors should be loaded. Default 'true'.
   *    @type bool $load_tags Whether tags should be loaded. Default 'true'.
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

      $this->meta = get_post_meta($this->wp_post->ID);
      $this->initialize_wp_post();
    } elseif (is_array($wp_post)) {
      $this->passle_post = $wp_post;
      $this->initialize_passle_post();
    } else {
      return;
    }

    $this->load_authors = (bool) $options["load_authors"];
    $this->load_tags = (bool) $options["load_tags"];

    if ($this->load_authors) {
      $this->initialize_authors();
    }

    if ($this->load_tags) {
      $post_tag_names = isset($this->meta["post_tags"])
        ? $this->meta["post_tags"]
        : ($this->passle_post["Tags"] ?? []);

      $post_tag_names = $this->normalize_tag_names($post_tag_names);
      $all_wp_tags = $this->get_wp_tags_by_names($post_tag_names);

      if (!empty($all_wp_tags)) {
        update_meta_cache('term', array_map(fn($term) => $term->term_id, $all_wp_tags));
      }

      $this->initialize_tags($all_wp_tags);
      $this->initialize_tag_groups($all_wp_tags);
    } else {
      $this->tags = isset($this->meta["post_tags"])
        ? ($this->meta["post_tags"] ?? [])
        : ($this->passle_post["Tags"] ?? []);

      $this->tag_groups = isset($this->meta["post_tag_groups"])
        ? ($this->meta["post_tag_groups"] ?? [])
        : ($this->passle_post["TagGroups"] ?? []);
    }

    $this->initialize_share_views();
  }

  /**
   * Get the date in the specified format.
   *
   * @param string $format The formatting options for the string.
   * @return string
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
    return array_map(fn ($tag) => $tag->name, $this->tags ?? []);
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

  /** @internal */
  private function initialize_wp_post()
  {
    $this->shortcode = $this->meta["post_shortcode"][0] ?? "";
    $this->passle_shortcode = $this->meta["passle_shortcode"][0] ?? "";
    $this->url = $this->meta["post_url"][0] ?? "";
    $this->slug = $this->meta["post_slug"][0] ?? "";
    $this->title = $this->wp_post->post_title ?? "";
    $this->content = $this->wp_post->post_content ?? "";
    $this->total_shares = (int) ($this->meta["post_total_shares"][0] ?? 0);
    $this->total_likes = (int) ($this->meta["post_total_likes"][0] ?? 0);
    $this->date = date_create($this->wp_post->post_date ?? "now");
    $this->tags = $this->meta["post_tags"] ?? [];
    $this->tag_groups = $this->meta["post_tag_groups"] ?? [];
    $this->is_repost = (bool) ($this->meta["post_is_repost"][0] ?? false);
    $this->estimated_read_time_seconds = (int) ($this->meta["post_estimated_read_time"][0] ?? 0);
    $this->estimated_read_time_minutes = max((int) ceil($this->estimated_read_time_seconds / 60), 1);
    $this->image_url = $this->meta["post_image_url"][0] ?? "";
    $this->featured_item_html = htmlspecialchars_decode($this->meta["post_featured_item_html"][0] ?? "", ENT_QUOTES);
    $this->featured_item_position = $this->meta["post_featured_item_position"][0] ?? 0;
    $this->featured_item_media_type = $this->meta["post_featured_item_media_type"][0] ?? 0;
    $this->featured_item_embed_type = $this->meta["post_featured_item_embed_type"][0] ?? 0;
    $this->featured_item_embed_provider = $this->meta["post_featured_item_embed_provider"][0] ?? "";
    $this->excerpt = $this->wp_post->post_excerpt ?? "";
    $this->opens_in_new_tab = (bool) ($this->meta["post_opens_in_new_tab"][0] ?? false);
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
    $this->total_shares = (int) ($this->passle_post["TotalShares"] ?? 0);
    $this->total_likes = (int) ($this->passle_post["TotalLikes"] ?? 0);
    $this->date = $this->initialize_passle_date($this->passle_post["PublishedDate"] ?? "now");
    $this->tags = $this->passle_post["Tags"] ?? [];
    $this->tag_groups = $this->passle_post["TagGroups"] ?? [];
    $this->is_repost = (bool) ($this->passle_post["IsRepost"] ?? false);
    $this->estimated_read_time_seconds = (int) ($this->passle_post["EstimatedReadTimeSeconds"] ?? 0);
    $this->estimated_read_time_minutes = max((int) ceil($this->estimated_read_time_seconds / 60), 1);
    $this->image_url = $this->passle_post["ImageUrl"] ?? "";
    $this->featured_item_html = htmlspecialchars_decode($this->passle_post["FeaturedItemHtml"] ?? "");
    $this->featured_item_position = $this->passle_post["FeaturedItemPosition"] ?? 0;
    $this->featured_item_media_type = $this->passle_post["FeaturedItemMediaType"] ?? 0;
    $this->featured_item_embed_type = $this->passle_post["FeaturedItemEmbedType"] ?? 0;
    $this->featured_item_embed_provider = $this->passle_post["FeaturedItemEmbedProvider"] ?? "";
    $this->excerpt = $this->passle_post["ContentTextSnippet"] ?? "";
    $this->opens_in_new_tab = (bool) ($this->passle_post["OpensInNewTab"] ?? false);
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

    if ($year <= 1970) {
      return date_create("now");
    }

    return date_create($date);
  }

  private function initialize_authors()
  {
    $wp_authors = $this->get_wp_authors_for_shortcode($this->shortcode);

    $this->authors = $this->map_authors("post_author_shortcodes", "post_authors", "Authors", $wp_authors);
    $this->coauthors = $this->map_authors("post_coauthor_shortcodes", "post_coauthors", "CoAuthors", $wp_authors);

    if (!empty($this->authors)) {
      $this->primary_author = $this->authors[0];
    } else {
      $this->primary_author = new PassleAuthor([
        'name' => 'Deleted',
        'shortcode' => '',
        'profile_url' => '',
        'image_url' => PASSLESYNC_DEFAULT_PROFILE_IMAGE,
        'role' => ''
      ]);
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

    $this->wp_tag_lookup = [];

    foreach ($wp_tags as $wp_tag) {
      $this->wp_tag_lookup[html_entity_decode($wp_tag->name)] = $wp_tag;
    }

    $this->tags = $this->map_tags($tags);
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

      $this->tag_groups[] = [
        "name" => $unserialized_tag_group["Name"] ?? "",
        "tags" => $this->map_tags((array) $group_tags),
      ];

      unset($unserialized_tag_group);
    }
  }

  private function initialize_share_views()
  {
    if (isset($this->meta["post_share_views"])) {
      $share_views = $this->meta["post_share_views"];
    } else {
      $share_views = $this->passle_post["ShareViews"] ?? [];
    }

    $this->share_views = $this->map_share_views($share_views ?? []);
  }

  private function map_authors(string $shortcode_meta_key, string $author_meta_key, string $author_post_key, array $wp_authors)
  {
    if (isset($this->meta[$author_meta_key])) {
      $post_authors = array_map(function ($author) {
        $value = maybe_unserialize($author);
        return is_array($value) ? $value : [];
      }, $this->meta[$author_meta_key] ?? []);

      if (isset($this->meta[$shortcode_meta_key])) {
        $author_shortcodes = $this->meta[$shortcode_meta_key];
      } else {
        $author_shortcodes = Utils::array_select($post_authors, "shortcode");
      }
    } else {
      $post_authors = PostHandler::map_authors($this->passle_post[$author_post_key] ?? []);
      $author_shortcodes = Utils::array_select($post_authors, "shortcode");
    }

    $wp_author_lookup = [];
    foreach ($wp_authors as $wp_author) {
      $wp_author_lookup[$wp_author->post_name] = $wp_author;
    }

    $post_author_lookup = [];
    foreach ($post_authors as $post_author) {
      if (!empty($post_author["shortcode"])) {
        $post_author_lookup[$post_author["shortcode"]] = $post_author;
      }
    }

    $authors = array_map(function ($author_shortcode) use ($wp_author_lookup, $post_author_lookup) {
      return $wp_author_lookup[$author_shortcode]
        ?? $post_author_lookup[$author_shortcode]
        ?? null;
    }, $author_shortcodes ?? []);

    $authors = array_filter($authors);

    return array_map(fn ($author) => new PassleAuthor($author), $authors);
  }

  private function map_tags(array $tags)
  {
    if (empty($tags)) {
      return [];
    }

    $result = [];

    foreach ($tags as $tag) {
      $decoded_tag = html_entity_decode((string) $tag);
      $wp_tag = $this->wp_tag_lookup[$decoded_tag] ?? null;
      $aliases = [];

      if ($wp_tag) {
        $term_id = (int) $wp_tag->term_id;

        if (!array_key_exists($term_id, self::$alias_cache)) {
          $aliases = get_term_meta($term_id, 'aliases', true);
          self::$alias_cache[$term_id] = is_array($aliases) ? $aliases : [];
        }

        $aliases = self::$alias_cache[$term_id];
      }

      $result[] = new PassleTag($tag, $wp_tag, $aliases);
    }

    return $result;
  }

  private function map_share_views(array $share_views)
  {
    if (isset($this->meta["post_share_views"])) {
      return array_map(function ($network) {
        $value = maybe_unserialize($network);
        return new PassleShareViewsNetwork(is_array($value) ? $value : []);
      }, $share_views);
    }

    return array_map(
      fn ($network) => new PassleShareViewsNetwork($network),
      PostHandler::map_share_views($share_views)
    );
  }

  private function normalize_tag_names(array $tag_names): array
  {
    $normalized = array_map(function ($tag) {
      if (!is_scalar($tag)) {
        return '';
      }

      return trim(html_entity_decode((string) $tag));
    }, $tag_names);

    $normalized = array_filter($normalized, fn($tag) => $tag !== '');
    $normalized = array_values(array_unique($normalized));

    if (count($normalized) > self::MAX_TAG_LOOKUP_COUNT) {
      $normalized = array_slice($normalized, 0, self::MAX_TAG_LOOKUP_COUNT);
    }

    return $normalized;
  }

  private function get_wp_tags_by_names(array $post_tag_names): array
  {
    if (empty($post_tag_names)) {
      return [];
    }

    sort($post_tag_names);
    $cache_key = md5(wp_json_encode($post_tag_names));

    if (!isset(self::$term_lookup_cache[$cache_key])) {
      $terms = get_terms([
        'taxonomy' => 'post_tag',
        'name__in' => $post_tag_names,
        'hide_empty' => false,
        'update_term_meta_cache' => false,
      ]);

      self::$term_lookup_cache[$cache_key] = is_wp_error($terms) ? [] : $terms;
    }

    return self::$term_lookup_cache[$cache_key];
  }

  private function get_wp_authors_for_shortcode(string $shortcode): array
  {
    $shortcode = trim($shortcode);

    if ($shortcode === '') {
      return [];
    }

    if (!isset(self::$author_cache[$shortcode])) {
      self::$author_cache[$shortcode] = get_posts([
        'post_type' => PASSLESYNC_AUTHOR_TYPE,
        'posts_per_page' => -1,
        'meta_key' => 'post_shortcode',
        'meta_value' => $shortcode,
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'suppress_filters' => true,
      ]);
    }

    return self::$author_cache[$shortcode];
  }
}