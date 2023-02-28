<?php

namespace Passle\PassleSync\Models\Admin;

use JsonSerializable;

class Options implements JsonSerializable
{
  public string $passle_api_key;
  public string $plugin_api_key;
  /** @var string[] $passle_shortcodes */
  public array $passle_shortcodes;
  public string $post_permalink_prefix;
  public string $person_permalink_prefix;
  public string $preview_permalink_prefix;
  public bool $include_passle_posts_on_home_page;
  public bool $include_passle_posts_on_tag_page;
  public string $domain_ext;
  public string $site_url;

  /** @param string[] $passle_shortcodes */
  public function __construct(
    string $passle_api_key,
    string $plugin_api_key,
    array $passle_shortcodes,
    string $post_permalink_prefix,
    string $person_permalink_prefix,
    string $preview_permalink_prefix,
    bool $include_passle_posts_on_home_page,
    bool $include_passle_posts_on_tag_page,
    string $domain_ext,
    string $site_url
  ) {
    $this->passle_api_key = $passle_api_key;
    $this->plugin_api_key = $plugin_api_key;
    $this->passle_shortcodes = $passle_shortcodes;
    $this->post_permalink_prefix = $post_permalink_prefix;
    $this->person_permalink_prefix = $person_permalink_prefix;
    $this->preview_permalink_prefix = $preview_permalink_prefix;
    $this->include_passle_posts_on_home_page = $include_passle_posts_on_home_page;
    $this->include_passle_posts_on_tag_page = $include_passle_posts_on_tag_page;
    $this->domain_ext = $domain_ext;
    $this->site_url = $site_url;
  }

  public function jsonSerialize()
  {
    return [
      "passleApiKey" => $this->passle_api_key,
      "pluginApiKey" => $this->plugin_api_key,
      "passleShortcodes" => $this->passle_shortcodes,
      "postPermalinkPrefix" => $this->post_permalink_prefix,
      "personPermalinkPrefix" => $this->person_permalink_prefix,
      "previewPermalinkPrefix" => $this->preview_permalink_prefix,
      "includePasslePostsOnHomePage" => isset($this->include_passle_posts_on_home_page) ? $this->include_passle_posts_on_home_page : false,
      "includePasslePostsOnTagPage" => isset($this->include_passle_posts_on_tag_page) ? $this->include_passle_posts_on_tag_page : false,
      "domainExt" => $this->domain_ext,
      "siteUrl" => $this->site_url,
    ];
  }
}
