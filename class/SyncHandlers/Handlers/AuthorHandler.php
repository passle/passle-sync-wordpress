<?php

namespace Passle\PassleSync\SyncHandlers\Handlers;

use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\ISyncHandler;
use Passle\PassleSync\Services\Content\PeopleWordpressContentService;
use Passle\PassleSync\Services\PassleContentService;

class AuthorHandler extends SyncHandlerBase implements ISyncHandler
{
  private $shortcodeKey = "Shortcode";
  private $wordpress_content_service;

  public function __construct(
    PeopleWordpressContentService $wordpress_content_service,
    PassleContentService $passle_content_service
  ) {
    parent::__construct($passle_content_service);
    $this->wordpress_content_service = $wordpress_content_service;
  }

  protected function sync_all_impl()
  {
    $passle_authors = $this->passle_content_service->get_stored_passle_authors_from_api();
    $existing_authors = $this->wordpress_content_service->get_items();

    return $this->compare_items($passle_authors, $existing_authors, $this->shortcodeKey, 'author_shortcode');
  }

  protected function sync_one_impl(array $data)
  {
    $existing_author = $this->wordpress_content_service->get_item_by_shortcode($data[$this->shortcodeKey], 'author_shortcode');

    if ($existing_author == null) {
      $this->sync(null, $data);
    } else {
      $this->sync($existing_author, $data);
    }
  }

  protected function delete_all_impl()
  {
    $existing_authors = $this->wordpress_content_service->get_items();

    foreach ($existing_authors as $author) {
      $this->delete($author);
    }
  }

  protected function delete_one_impl(array $data)
  {
    $existing_author = $this->wordpress_content_service->get_item_by_shortcode($data[$this->shortcodeKey], 'author_shortcode');

    if ($existing_author != null) {
      $this->delete($existing_author);
    }
  }

  protected function sync(?object $author, array $data)
  {
    // Find if there's an existing author with this shortcode
    // Update it, if so
    $id = 0;
    $existing_author = $this->wordpress_content_service->get_item_by_shortcode($data['Shortcode'], 'author_shortcode');
    if ($existing_author != null) {
      $id = $existing_author->ID;
    }

    // Update the fields from the new data
    $author_name = $data["Name"];
    $author_shortcode = $data["Shortcode"];
    $profile_url = $data["ProfileUrl"];
    $avatar_url = $data["AvatarUrl"];
    $author_role = $data["RoleInfo"];
    $author_description = $data["Description"];
    $email_address = $data["EmailAddress"];
    $phone_number = $data["PhoneNumber"];
    $linkedin_profile_link = $data["LinkedInProfileLink"];
    $facebook_profile_link = $data["FacebookProfileLink"];
    $twitter_screen_name = $data["TwitterScreenName"];
    $xing_profile_link = $data["XingProfileLink"];
    $skype_profile_link = $data["SkypeProfileLink"];
    $vimeo_profile_link = $data["VimeoProfileLink"];
    $youtube_profile_link = $data["YouTubeProfileLink"];
    $stumbleupon_profile_link = $data["StumbleUponProfileLink"];
    $pinterest_profile_link = $data["PinterestProfileLink"];
    $instagram_profile_link = $data["InstagramProfileLink"];
    $personal_links = $this->map_links($data["PersonalLinks"] ?? []);
    $location_detail = $data["LocationDetail"];
    $location_country = $data["LocationCountry"];
    $company_tagline = $data["TagLineCompany"];

    $new_item = [
      "ID" => $id,
      "post_title" => $author_name,
      "post_name" => $author_shortcode,
      "post_type" => PASSLESYNC_AUTHOR_TYPE,
      "post_content" => $author_description,
      "post_excerpt" => $author_role,
      "post_status" => "publish",
      "comment_status" => "closed",
      "meta_input" => [
        "author_shortcode" => $author_shortcode,
        "avatar_url" => $avatar_url,
        "profile_url" => $profile_url,
        "email_address" => $email_address,
        "phone_number" => $phone_number,
        "linkedin_profile_link" => $linkedin_profile_link,
        "facebook_profile_link" => $facebook_profile_link,
        "twitter_screen_name" => $twitter_screen_name,
        "xing_profile_link" => $xing_profile_link,
        "skype_profile_link" => $skype_profile_link,
        "vimeo_profile_link" => $vimeo_profile_link,
        "youtube_profile_link" => $youtube_profile_link,
        "stumbleupon_profile_link" => $stumbleupon_profile_link,
        "pinterest_profile_link" => $pinterest_profile_link,
        "instagram_profile_link" => $instagram_profile_link,
        "personal_links" => $personal_links,
        "location_detail" => $location_detail,
        "location_country" => $location_country,
        "company_tagline" => $company_tagline,
      ]
    ];

    $new_id = $this->insert_post($new_item, true);
    if ($new_id != $id) {
      $new_item["ID"] = $new_id;
    }

    return $new_item;
  }

  protected function delete(object $author)
  {
    $this->delete_item($author->ID);
  }

  private function map_links(array $links)
  {
    return array_map(fn ($link) => [
      "title" => $link["Title"],
      "url" => $link["Url"],
    ], $links);
  }
}
