<?php

namespace Passle\PassleSync\Models\Admin;

class Post
{
  public string $shortcode;
  public string $postUrl;
  public string $imageUrl;
  public string $title;
  public string $authors;
  public string $excerpt;
  public string $body;
  public string $publishedDate;
  public bool $synced;

  public function __construct(
    string $shortcode,
    string $postUrl,
    string $imageUrl,
    string $title,
    string $authors,
    string $excerpt,
    string $body,
    string $publishedDate,
    bool $synced
  ) {
    $this->shortcode = $shortcode;
    $this->postUrl = $postUrl;
    $this->imageUrl = $imageUrl;
    $this->title = $title;
    $this->authors = $authors;
    $this->excerpt = $excerpt;
    $this->body = $body;
    $this->publishedDate = $publishedDate;
    $this->synced = $synced;
  }

  public static function fromApiEntity(array $from)
  {
    $authors = join(", ", array_map(fn ($author) => $author["Name"], $from["Authors"])) ?? "";

    return new self(
      $from["PostShortcode"],
      $from["PostUrl"],
      $from["ImageUrl"],
      $from["PostTitle"],
      $authors,
      $from["ContentTextSnippet"],
      $from["PostContentHtml"],
      strtotime($from["PublishedDate"]),
      false,
    );
  }

  public static function fromWordpressEntity(object $from)
  {
    $authors = "";
    if (!empty($from->post_authors)) {
      $authors = join(", ", array_map(fn ($author) => unserialize($author)["name"], $from->post_authors));
    }

    $shortcode = "";
    if (!empty($from->post_shortcode) && count($from->post_shortcode) > 0) {
      $shortcode = $from->post_shortcode[0];
    }

    $post_url = "";
    if (!empty($from->post_url) && count($from->post_url) > 0) {
      $post_url = $from->post_url[0];
    }

    $post_image_url = "";
    if (!empty($from->post_image_url) && count($from->post_image_url) > 0) {
      $post_image_url = $from->post_image_url[0];
    }


    return new self(
      $shortcode,
      $post_url,
      $post_image_url,
      $from->post_title,
      $authors,
      $from->post_excerpt,
      $from->post_content,
      strtotime($from->post_date),
      true,
    );
  }

  public function to_array()
  {
    return get_object_vars($this);
  }
}
