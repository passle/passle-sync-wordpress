<?php

namespace Passle\PassleSync\Models;

class Person
{
  public string $shortcode;
  public string $profileUrl;
  public string $avatarUrl;
  public string $name;
  public string $role;
  public string $description;
  public bool $synced;

  public function __construct(
    string $shortcode,
    string $profileUrl,
    ?string $avatarUrl,
    string $name,
    ?string $role,
    ?string $description,
    bool $synced
  ) {
    $this->shortcode = $shortcode;
    $this->profileUrl = $profileUrl;
    $this->avatarUrl = $avatarUrl ?? "";
    $this->name = $name;
    $this->role = $role ?? "";
    $this->description = $description ?? "";
    $this->synced = $synced;
  }

  public static function fromPasslePerson(array $from)
  {
    return new self(
      $from["Shortcode"],
      $from["ProfileUrl"],
      $from["AvatarUrl"],
      $from["Name"],
      $from["RoleInfo"],
      $from["Description"],
      false,
    );
  }

  public static function fromWordpressPerson(object $from)
  {
    return new self(
      $from->author_shortcode,
      $from->guid,
      $from->avatar_url,
      $from->post_title,
      $from->post_excerpt,
      $from->post_content,
      true,
    );
  }

  public function to_array()
  {
    return get_object_vars($this);
  }
}
