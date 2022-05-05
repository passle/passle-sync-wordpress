<?php

namespace Passle\PassleSync\Models\Admin;

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
      wp_trim_words($from["Description"], 20),
      false,
    );
  }

  public static function fromWordpressPerson(object $from)
  {
    return new self(
      $from->author_shortcode[0],
      $from->profile_url[0],
      $from->avatar_url[0],
      $from->post_title,
      $from->post_excerpt,
      wp_trim_words($from->post_content, 20),
      true,
    );
  }

  public function to_array()
  {
    return get_object_vars($this);
  }
}
