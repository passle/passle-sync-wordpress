<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle Tweets that have been saved to the Wordpress database.
 */
class PassleTweet
{
  public string $embed_code;
  public string $tweet_id;
  public string $screen_name;

  private array $wp_tweet;

  public function __construct(array $wp_tweet)
  {
    $this->wp_tweet = $wp_tweet;
    $this->initialize();
  }

  private function initialize()
  {
    $this->embed_code = $this->wp_tweet["embed_code"];
    $this->tweet_id = $this->wp_tweet["tweet_id"];
    $this->screen_name = $this->wp_tweet["screen_name"];
  }
}
