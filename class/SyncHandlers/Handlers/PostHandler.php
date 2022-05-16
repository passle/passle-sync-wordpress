<?php

namespace Passle\PassleSync\SyncHandlers\Handlers;

use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\ISyncHandler;
use Passle\PassleSync\Services\Content\PostsWordpressContentService;
use Passle\PassleSync\Services\PassleContentService;

class PostHandler extends SyncHandlerBase implements ISyncHandler
{
  private $shortcodeKey = "PostShortcode";
  private $wordpress_content_service;

  public function __construct(
    PostsWordpressContentService $wordpress_content_service,
    PassleContentService $passle_content_service
  ) {
    parent::__construct($passle_content_service);
    $this->wordpress_content_service = $wordpress_content_service;
  }

  protected function sync_all_impl()
  {
    $passle_posts = $this->passle_content_service->get_stored_passle_posts_from_api();
    $existing_posts = $this->wordpress_content_service->get_items();

    return $this->compare_items($passle_posts, $existing_posts, $this->shortcodeKey, 'post_shortcode');
  }

  protected function sync_one_impl(array $data)
  {
    $existing_post = $this->wordpress_content_service->get_item_by_shortcode($data[$this->shortcodeKey], 'post_shortcode');

    if ($existing_post == null) {
      return $this->sync(null, $data);
    } else {
      return $this->sync($existing_post, $data);
    }
  }

  protected function delete_all_impl()
  {
    $existing_posts = $this->wordpress_content_service->get_items();

    $response = true;
    foreach ($existing_posts as $post) {
      $response &= $this->delete($post);
    }
    return $response;
  }

  protected function delete_one_impl(array $data)
  {
    $existing_post = $this->wordpress_content_service->get_item_by_shortcode($data[$this->shortcodeKey], 'post_shortcode');

    if ($existing_post != null) {
      return $this->delete($existing_post);
    }
  }

  protected function sync(?object $post, array $data)
  {
    // Find if there's an existing post with this shortcode
    // Update it, if so
    $id = 0;
    $existing_post = $this->wordpress_content_service->get_item_by_shortcode($data['PostShortcode'], 'post_shortcode');
    if ($existing_post != null) {
      $id = $existing_post->ID;
    }

    // Update the fields from the new data
    $post_shortcode = $data["PostShortcode"];
    $passle_shortcode = $data["PassleShortcode"];
    $post_url = $data["PostUrl"];
    $post_slug = $this->extract_slug_from_url($data["PostUrl"]);
    $post_title = $data["PostTitle"];
    $post_content = $data["PostContentHtml"];
    $post_authors = $this->map_authors($data["Authors"]);
    $post_author_shortcodes = $this->map_author_shortcodes($data["Authors"]);
    $post_coauthors = $this->map_authors($data["CoAuthors"]);
    $post_coauthor_shortcodes = $this->map_author_shortcodes($data["CoAuthors"]);
    $post_share_views = $this->map_share_views($data["ShareViews"]);
    $post_tweets = $this->map_tweets($data["Tweets"]);
    $post_total_shares = $data["TotalShares"];
    $post_total_likes = $data["TotalLikes"];
    $post_date = $data["PublishedDate"];
    $post_tags = $data["Tags"];
    $post_is_repost = $data["IsRepost"];
    $post_estimated_read_time = $data["EstimatedReadTimeInSeconds"];
    $post_image_url = $data["ImageUrl"];
    $post_featured_item_html = $data["FeaturedItemHtml"];
    $post_featured_item_position = $data["FeaturedItemPosition"];
    $post_featured_item_media_type = $data["FeaturedItemMediaType"];
    $post_featured_item_embed_type = $data["FeaturedItemEmbedType"];
    $post_featured_item_embed_provider = $data["FeaturedItemEmbedProvider"];
    $post_excerpt = $data["ContentTextSnippet"];
    $post_opens_in_new_tab = $data["OpensInNewTab"];
    $post_quote_text = $data["QuoteText"];
    $post_quote_url = $data["QuoteUrl"];

    $new_item = [
      "ID" => $id,
      "post_title" => $post_title,
      "post_name" => $post_shortcode,
      "post_date" => $post_date,
      "post_type" => PASSLESYNC_POST_TYPE,
      "post_content" => $post_content,
      "post_excerpt" => $post_excerpt,
      "post_status" => "publish",
      "comment_status" => "closed",
      "meta_input" => [
        "post_shortcode" => $post_shortcode,
        "passle_shortcode" => $passle_shortcode,
        "post_url" => $post_url,
        "post_slug" => $post_slug,
        "post_authors" => $post_authors,
        "post_author_shortcodes" => $post_author_shortcodes,
        "post_coauthors" => $post_coauthors,
        "post_coauthors_shortcodes" => $post_coauthor_shortcodes,
        "post_share_views" => $post_share_views,
        "post_tweets" => $post_tweets,
        "post_total_shares" => $post_total_shares,
        "post_total_likes" => $post_total_likes,
        "post_is_repost" => $post_is_repost,
        "post_estimated_read_time" => $post_estimated_read_time,
        "post_tags" => $post_tags,
        "post_image_url" => $post_image_url,
        "post_featured_item_html" => $post_featured_item_html,
        "post_featured_item_position" => $post_featured_item_position,
        "post_featured_item_media_type" => $post_featured_item_media_type,
        "post_featured_item_embed_type" => $post_featured_item_embed_type,
        "post_featured_item_embed_provider" => $post_featured_item_embed_provider,
        "post_opens_in_new_tab" => $post_opens_in_new_tab,
        "post_quote_text" => $post_quote_text,
        "post_quote_url" => $post_quote_url,
      ],
    ];

    $new_id = $this->insert_post($new_item, true);
    if ($new_id != $id) {
      $new_item["ID"] = $new_id;
    }

    return $new_item;
  }

  protected function delete(object $post)
  {
    return $this->delete_item($post->ID);
  }

  private function map_authors(array $authors)
  {
    return array_map(fn ($author) => [
      "shortcode" => $author["Shortcode"],
      "name" => $author["Name"],
      "image_url" => $author["ImageUrl"],
      "profile_url" => $author["ProfileUrl"],
      "role" => $author["Role"],
      "twitter_screen_name" => $author["TwitterScreenName"],
    ], $authors);
  }

  private function map_author_shortcodes(array $authors)
  {
    return array_map(fn ($author) => $author["Shortcode"], $authors);
  }

  private function map_share_views(array $share_views)
  {
    return array_map(fn ($share_view) => [
      "social_network" => $share_view["SocialNetwork"],
      "total_views" => $share_view["TotalViews"],
    ], $share_views);
  }

  private function map_tweets(array $tweets)
  {
    return array_map(fn ($tweet) => [
      "embed_code" => $tweet["EmbedCode"],
      "tweet_id" => $tweet["TweetId"],
      "screen_name" => $tweet["ScreenName"],
    ], $tweets);
  }

  private function extract_slug_from_url(string $url)
  {
    return basename($url);
  }
}
