<?php

namespace Passle\PassleSync\Models;

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

  public static function fromPasslePost(array $from)
  {
    $authors = join(", ", array_map(fn ($author) => $author["Name"], $from["Authors"]));

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

  public static function fromWordpressPost(object $from)
  {
    return new self(
      $from->post_shortcode,
      $from->guid,
      $from->post_image,
      $from->post_title,
      $from->post_authors,
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
