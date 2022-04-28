<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle authors that have been saved to the Wordpress database.
 */
class PassleAuthor
{
  public string $name;
  public string $shortcode;
  public string $passle_shortcode;
  public string $profile_url;
  public string $avatar_url;
  public string $role;
  public string $description;

  private object $wp_author;

  public function __construct(object $wp_author)
  {
    $this->wp_author = $wp_author;
    $this->initialize();
  }

  private function initialize()
  {
    $this->name = $this->wp_author->name;
    $this->shortcode = $this->wp_author->shortcode;
    $this->passle_shortcode = $this->wp_author->passle_shortcode;
    $this->profile_url = $this->wp_author->profile_url;
    $this->avatar_url = $this->wp_author->avatar_url;
    $this->role = $this->wp_author->role;
    $this->description = $this->wp_author->description;
  }
}
