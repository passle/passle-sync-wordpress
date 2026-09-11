<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Models\Resources\PersonResource;
use Passle\PassleSync\SyncHandlers\SyncHandlerBase;

class AuthorHandler extends SyncHandlerBase
{
  const RESOURCE = PersonResource::class;

  /** No pre-sync setup needed for authors. */
  protected static function pre_sync_all_hook()
  { }

  /** Fires an action other code can hook into once every author has been synced. */
  protected static function post_sync_all_hook()
  {
    do_action("passle_author_sync_all_complete");
  }

  /** Clears any stale legacy pending-deletion flag on this author and fires a per-entity completion action. */
  protected static function post_sync_one_hook(int $entity_id)
  {
    delete_post_meta($entity_id, '_pending_deletion');
    do_action("passle_author_sync_one_complete", $entity_id);
  }

  /** Reads the stored page-resume option for authors (defaults to page 1). */
  protected static function get_last_synced_page()
  {
    $resource = static::get_resource_instance();
    $last_synced_page = get_option($resource->last_synced_page_option_name);
    return $last_synced_page !== false ? $last_synced_page : 1;
  }

  /** Persists the page-resume option for authors. */
  protected static function set_last_synced_page(int $page_number)
  {
    $resource = static::get_resource_instance();
    update_option($resource->last_synced_page_option_name, $page_number);
  }

  /**
   * No-op: unlike posts, authors are never deleted by the paginated sync. The Passle
   * people API's coverage for a shortcode hasn't been verified to be complete (no date
   * field to build a safe comparison window from either), so deletion stays webhook-only
   * (see delete_one()/delete_many()) until that's confirmed.
   */
  protected static function post_page_sync_hook(string $url, array $api_entities, int $page_number, int $total_pages)
  { }

  /** Maps a Passle person record to a WP postarr for the author CPT. Preserves the existing post_date on updates so re-syncing doesn't restamp it to "now". */
  protected static function map_data(array $data, int $entity_id)
  {
    $postarr = [
      "ID" => $entity_id,
      "post_title" => $data["Name"],
      "post_name" => $data["Shortcode"],
      "post_type" => PASSLESYNC_AUTHOR_TYPE,
      "post_content" => $data["Description"] ?? "",
      "post_excerpt" => $data["RoleInfo"] ?? "",
      "post_status" => "publish",
      "comment_status" => "closed",
      "meta_input" => [
        "author_shortcode" => $data["Shortcode"],
        "author_slug" => static::extract_slug_from_url($data["ProfileUrl"]),
        "avatar_url" => $data["AvatarUrl"],
        "profile_url" => $data["ProfileUrl"],
        "subscribe_link" => $data["SubscribeLink"],
        "email_address" => $data["EmailAddress"],
        "public_email_address" => $data["PublicEmailAddress"],
        "primary_email_address" => $data["PrimaryEmailAddress"],
        "phone_number" => $data["PhoneNumber"],
        "linkedin_profile_link" => $data["LinkedInProfileLink"],
        "facebook_profile_link" => $data["FacebookProfileLink"],
        "twitter_screen_name" => $data["TwitterScreenName"],
        "xing_profile_link" => $data["XingProfileLink"],
        "skype_profile_link" => $data["SkypeProfileLink"],
        "vimeo_profile_link" => $data["VimeoProfileLink"],
        "youtube_profile_link" => $data["YouTubeProfileLink"],
        "stumbleupon_profile_link" => $data["StumbleUponProfileLink"],
        "pinterest_profile_link" => $data["PinterestProfileLink"],
        "instagram_profile_link" => $data["InstagramProfileLink"],
        "personal_links" => static::map_links($data["PersonalLinks"] ?? []),
        "location_detail" => $data["LocationDetail"],
        "location_country" => $data["LocationCountry"],
        "company_tagline" => $data["TagLineCompany"],
      ]
    ];

    if ($entity_id) {
      $existing_post = get_post($entity_id);
      if ($existing_post) {
        $postarr["post_date"] = $existing_post->post_date;
      }
    }

    return $postarr;
  }

  /** Maps raw Passle personal-link data to the format stored in the `personal_links` meta field. */
  private static function map_links(array $links)
  {
    return array_map(fn ($link) => [
      "title" => $link["Title"],
      "url" => $link["Url"],
    ], $links);
  }
}
