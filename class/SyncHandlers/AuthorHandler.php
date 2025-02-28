<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Models\Resources\PersonResource;
use Passle\PassleSync\SyncHandlers\SyncHandlerBase;

class AuthorHandler extends SyncHandlerBase
{
  const RESOURCE = PersonResource::class;

  protected static function pre_sync_all_hook()
  { }

  protected static function post_sync_all_hook()
  { 
    // Reset last synced page for authors
    $resource = static::get_resource_instance();
    update_option($resource->last_synced_page_option_name, 1);
    do_action("passle_author_sync_all_complete");
  }

  protected static function post_sync_one_hook(int $entity_id)
  { 
    delete_post_meta($entity_id, '_pending_deletion');
    do_action("passle_author_sync_one_complete", $entity_id);
  }

  protected static function get_last_synced_page()
  {
    $resource = static::get_resource_instance();
    $last_synced_page = get_option($resource->last_synced_page_option_name);
    return $last_synced_page !== false ? $last_synced_page : 1;
  }

  protected static function set_last_synced_page(int $page_number)
  {
    $resource = static::get_resource_instance();
    update_option($resource->last_synced_page_option_name, $page_number);
  }

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

    return $postarr;
  }

  private static function map_links(array $links)
  {
    return array_map(fn ($link) => [
      "title" => $link["Title"],
      "url" => $link["Url"],
    ], $links);
  }
}
