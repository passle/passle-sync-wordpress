<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle author personal links that have been saved to the Wordpress database.
 */
class PassleLink
{
  public string $title;
  public string $url;

  private array $wp_link;

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
