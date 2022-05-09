<?php

namespace Passle\PassleSync\Models;

/**
 * This class provides a simple interface for accessing properties of
 * Passle authors that have been saved to the Wordpress database.
 */
class PassleAuthor
{
  /** The person's full name. */
  public string $name;
  /** The shortcode for the person. */
  public string $shortcode;
  /** The URL for the person's profile. */
  public string $profile_url;
  /** The URL for this person's avatar image. */
  public string $avatar_url;
  /** The URL to the subscribe page for this person. */
  public string $subscribe_link;
  /** The tagline for this person. */
  public string $role;
  /** The profile description for this person. */
  public string $description;
  /** The person's email address. */
  public string $email_address;
  /** The person's phone number. */
  public string $phone_number;
  /** The URL to the person's LinkedIn profile. */
  public string $linkedin_profile_link;
  /** The URL to the person's Facebook profile. */
  public string $facebook_profile_link;
  /** The person's Twitter screen name. */
  public string $twitter_screen_name;
  /** The URL to the person's Xing profile. */
  public string $xing_profile_link;
  /** The URL to the person's Skype profile. */
  public string $skype_profile_link;
  /** The URL to the person's Vimeo profile. */
  public string $vimeo_profile_link;
  /** The URL to the person's YouTube profile. */
  public string $youtube_profile_link;
  /** The URL to the person's StumbleUpon profile. */
  public string $stumbleupon_profile_link;
  /** The URL to the person's Pinterest profile. */
  public string $pinterest_profile_link;
  /** The URL to the person's Instagram profile. */
  public string $instagram_profile_link;
  /** 
   * A list of titles and urls for any other links the person may have added to their profile.
   * @var PassleLink[]|null
   */
  public ?array $personal_links;
  /** The city/region the person is in. */
  public string $location_detail;
  /** The country the person is in. */
  public string $location_country;
  /** The combined city/region and/or country the person is in, separated by a comma. */
  public string $location_full;
  /** The tagline for the client the person is in. */
  public string $company_tagline;

  private object $wp_author;
  private array $meta;

  private array $post_author;

  /**
   * Construct a new instance of the `PassleAuthor` class from the Wordpress post object.
   * 
   * @param WP_Post $wp_author The Wordpress post object.
   * @return void
   */
  public function __construct($wp_author)
  {
    if (gettype($wp_author) === "object") {
      $this->wp_author = $wp_author;
      $this->meta = get_post_meta($wp_author->ID);
      $this->initialize_wp_author();
    } else {
      $this->post_author = $wp_author;
      $this->initialize_post_author();
    }
  }

  /**
   * Get the avatar URL, opionally specifying a fallback URL.
   * 
   * @param string|null $fallback_url The fallback image URL if the author doesn't have an avatar.
   * @return string
   */
  public function get_avatar_url(?string $fallback_url = PASSLESYNC_DEFAULT_AVATAR_URL)
  {
    return empty($this->avatar_url) ? $fallback_url : $this->avatar_url;
  }

  private function initialize_wp_author()
  {
    $this->name = $this->wp_author->post_title ?? "";
    $this->shortcode = $this->wp_author->post_name ?? "";
    $this->profile_url = $this->meta["profile_url"][0] ?? "";
    $this->avatar_url = $this->meta["avatar_url"][0] ?? "";
    $this->subscribe_link = $this->meta["subscribe_link"][0] ?? "";
    $this->role = $this->wp_author->post_excerpt ?? "";
    $this->description = $this->wp_author->post_content ?? "";
    $this->email_address = $this->meta["email_address"][0] ?? "";
    $this->phone_number = $this->meta["phone_number"][0] ?? "";
    $this->linkedin_profile_link = $this->meta["linkedin_profile_link"][0] ?? "";
    $this->facebook_profile_link = $this->meta["facebook_profile_link"][0] ?? "";
    $this->twitter_screen_name = $this->meta["twitter_screen_name"][0] ?? "";
    $this->xing_profile_link = $this->meta["xing_profile_link"][0] ?? "";
    $this->skype_profile_link = $this->meta["skype_profile_link"][0] ?? "";
    $this->vimeo_profile_link = $this->meta["vimeo_profile_link"][0] ?? "";
    $this->youtube_profile_link = $this->meta["youtube_profile_link"][0] ?? "";
    $this->stumbleupon_profile_link = $this->meta["stumbleupon_profile_link"][0] ?? "";
    $this->pinterest_profile_link = $this->meta["pinterest_profile_link"][0] ?? "";
    $this->instagram_profile_link = $this->meta["instagram_profile_link"][0] ?? "";
    $this->location_detail = $this->meta["location_detail"][0] ?? "";
    $this->location_country = $this->meta["location_country"][0] ?? "";
    $this->location_full = (empty($this->location_detail) || empty($this->location_country))
      ? ""
      : implode(", ", [$this->location_detail, $this->location_country]);
    $this->company_tagline = $this->meta["company_tagline"][0] ?? "";

    $this->initialize_links();
  }

  private function initialize_links()
  {
    $links = $this->meta["personal_links"] ?? [];
    $this->personal_links = $this->map_links($links);
  }

  private function map_links(array $links)
  {
    return array_map(fn ($link) => new PassleLink(unserialize($link)), $links);
  }

  private function initialize_post_author()
  {
    $this->name = $this->post_author["name"] ?? "";
    $this->shortcode = $this->post_author["shortcode"] ?? "";
    $this->profile_url = $this->post_author["profile_url"] ?? "";
    $this->avatar_url = $this->post_author["image_url"] ?? "";
    $this->role = $this->post_author["role"] ?? "";
    $this->description = "";
    $this->email_address = "";
    $this->phone_number = "";
    $this->linkedin_profile_link = "";
    $this->facebook_profile_link = "";
    $this->twitter_screen_name = "";
    $this->xing_profile_link = "";
    $this->skype_profile_link = "";
    $this->vimeo_profile_link = "";
    $this->youtube_profile_link = "";
    $this->stumbleupon_profile_link = "";
    $this->pinterest_profile_link = "";
    $this->instagram_profile_link = "";
    $this->location_detail = "";
    $this->location_country = "";
    $this->location_full = "";
    $this->company_tagline = "";
    $this->personal_links = [];
  }
}
