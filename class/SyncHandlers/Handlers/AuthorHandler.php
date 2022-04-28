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

    // Update the fields from the new data, using the existing property values as a default
    $author_name = $this->update_property($author, "post_title", $data, "Name");
    $author_shortcode = $this->update_property($author, "author_shortcode", $data, "Shortcode");
    $passle_shortcode = $this->update_property($author, "passle_shortcode", $data, "PassleShortcode");
    $profile_url = $this->update_property($author, "profile_url", $data, "ProfileUrl");
    $avatar_url = $this->update_property($author, "avatar_url", $data, "AvatarUrl");
    $author_role = $this->update_property($author, "post_excerpt", $data, "RoleInfo");
    $author_description = $this->update_property($author, "post_content", $data, "Description");

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
        "passle_shortcode" => $passle_shortcode,
        "avatar_url" => $avatar_url,
        "profile_url" => $profile_url,
      ]
    ];

    $new_id = wp_insert_post($new_item, true);
    if ($new_id != $id) {
      $new_item["ID"] = $new_id;
    }

    return $new_item;
  }

  protected function delete(object $author)
  {
    $this->delete_item($author->ID);
  }
}
