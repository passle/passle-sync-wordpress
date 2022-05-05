<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle share view networks that have been saved to the Wordpress database.
 */
class PassleShareViewsNetwork
{
  public string $social_network;
  public int $total_views;

  private array $wp_network;

  public function __construct(array $wp_network)
  {
    $this->wp_network = $wp_network;
    $this->initialize();
  }

  private function initialize()
  {
    $this->social_network = $this->wp_network["social_network"] ?? "";
    $this->total_views = $this->wp_network["total_views"] ?? 0;
  }
}
