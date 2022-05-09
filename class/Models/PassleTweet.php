<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle Tweets that have been saved to the Wordpress database.
 */
class PassleTweet
{
  /** The HTML embed code of the Tweet. */
  public string $embed_code;
  /** The ID of the Tweet. */
  public string $tweet_id;
  /** The Twitter username of the person who posted this Tweet. */
  public string $screen_name;

  private array $wp_tweet;

  /** @internal */
  public function __construct(array $wp_tweet)
  {
    $this->wp_tweet = $wp_tweet;
    $this->initialize();
  }

  private function initialize()
  {
    $this->embed_code = $this->wp_tweet["embed_code"] ?? "";
    $this->tweet_id = $this->wp_tweet["tweet_id"] ?? "";
    $this->screen_name = $this->wp_tweet["screen_name"] ?? "";
  }
}
