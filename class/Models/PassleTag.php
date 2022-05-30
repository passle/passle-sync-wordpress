<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle tags that have been saved to the Wordpress database.
 */
class PassleTag
{
  /** The name of the tag. */
  public string $name;
  /** The slug for the tag. */
  public string $slug;
  /** The link to view posts associated with the tag. */
  public string $url;

  private ?object $wp_tag;

  /** @internal */
  public function __construct(string $name, ?object $wp_tag)
  {
    $this->name = $name;
    $this->wp_tag = $wp_tag;
    $this->initialize();
  }

  private function initialize()
  {
    $this->slug = $this->wp_tag->slug ?? "";

    if ($this->wp_tag) {
      $this->url = get_tag_link($this->wp_tag);
    } else {
      $this->url = "";
    }
  }
}
