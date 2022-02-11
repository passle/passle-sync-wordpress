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
  public string $publishedDate;

  public function __construct(
    string $shortcode,
    string $postUrl,
    string $imageUrl,
    string $title,
    string $authors,
    string $excerpt,
    string $publishedDate)
  {
    $this->shortcode = $shortcode;
    $this->postUrl = $postUrl;
    $this->imageUrl = $imageUrl;
    $this->title = $title;
    $this->authors = $authors;
    $this->excerpt = $excerpt;
    $this->publishedDate = $publishedDate;
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
      $from["PublishedDate"],
    );
  }

  public static function fromWordpressPost(object $from)
  {
    return new self(
      $from->post_shortcode,
      $from->guid,
      $from->post_image,
      $from->post_title,
      $from->post_author,
      $from->post_excerpt,
      $from->post_date,
    );
  }

  public function to_array()
  {
    return get_object_vars($this);
  }
}
