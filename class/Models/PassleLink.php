<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle author personal links that have been saved to the Wordpress database.
 */
class PassleLink
{
  /** The title of the link. */
  public string $title;
  /** The URL of the link. */
  public string $url;

  private array $wp_link;

  /** @internal */
  public function __construct(array $wp_link)
  {
    $this->wp_link = $wp_link;
    $this->initialize();
  }

  private function initialize()
  {
    $this->title = $this->wp_link["title"] ?? "";
    $this->url = $this->wp_link["url"] ?? "";
  }
}
