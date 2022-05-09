<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle share view networks that have been saved to the Wordpress database.
 */
class PassleShareViewsNetwork
{
  /** The social media channel this object represents. One of None; LinkedIn; Twitter; Facebook; DefaultShareButtons = LinkedIn, Twitter and Facebook combined; Xing; Email. */
  public string $social_network;
  /** The total number of views via this social media channel. */
  public int $total_views;

  private array $wp_network;

  /** @internal */
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
