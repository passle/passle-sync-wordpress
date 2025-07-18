<?php

namespace Passle\PassleSync\Models\Admin;

use JsonSerializable;

class Options implements JsonSerializable
{
  public string $passle_api_key;
  public string $plugin_api_key;
  /** @var string[] $passle_shortcodes */
  public array $passle_shortcodes;
  public string $post_permalink_template;
  public string $person_permalink_template;
  public string $preview_permalink_template;
  public bool $simulate_remote_hosting;
  public bool $include_passle_posts_on_home_page;
  public bool $include_passle_posts_on_tag_page;
  public bool $include_passle_tag_groups;
  public string $domain_ext;
  public string $site_url;

  /** @param string[] $passle_shortcodes */
  public function __construct(
    string $passle_api_key,
    string $plugin_api_key,
    array $passle_shortcodes,
    string $post_permalink_template,
    string $person_permalink_template,
    string $preview_permalink_template,
    bool $simulate_remote_hosting,
    bool $include_passle_posts_on_home_page,
    bool $include_passle_posts_on_tag_page,
    bool $include_passle_tag_groups
  ) {
    $this->passle_api_key = $passle_api_key;
    $this->plugin_api_key = $plugin_api_key;
    $this->passle_shortcodes = $passle_shortcodes;
    $this->post_permalink_template = $post_permalink_template;
    $this->person_permalink_template = $person_permalink_template;
    $this->preview_permalink_template = $preview_permalink_template;
    $this->simulate_remote_hosting = $simulate_remote_hosting;
    $this->include_passle_posts_on_home_page = $include_passle_posts_on_home_page;
    $this->include_passle_posts_on_tag_page = $include_passle_posts_on_tag_page;
    $this->include_passle_tag_groups = $include_passle_tag_groups;
    $this->domain_ext = PASSLESYNC_DOMAIN_EXT;
    $this->site_url = home_url();
  }

  // Added #[\ReturnTypeWillChange] attribute to decorate jsonSerialize method and suppress relevant warnings, 
  // as an alternative to declaring the following method like : public function jsonSerialize(): mixed
  // The reason is to maintain backwards compatibility as mixed is supported by PHP 8.1 and later.
  // Might need to change in the future.

  #[\ReturnTypeWillChange]
  public function jsonSerialize()
  {
    return [
      "passleApiKey" => $this->passle_api_key,
      "pluginApiKey" => $this->plugin_api_key,
      "passleShortcodes" => $this->passle_shortcodes,
      "postPermalinkTemplate" => $this->post_permalink_template,
      "personPermalinkTemplate" => $this->person_permalink_template,
      "previewPermalinkTemplate" => $this->preview_permalink_template,
      "simulateRemoteHosting" => $this->simulate_remote_hosting,
      "includePasslePostsOnHomePage" => isset($this->include_passle_posts_on_home_page) ? $this->include_passle_posts_on_home_page : false,
      "includePasslePostsOnTagPage" => isset($this->include_passle_posts_on_tag_page) ? $this->include_passle_posts_on_tag_page : false,
      "includePassleTagGroups" => isset($this->include_passle_tag_groups) ? $this->include_passle_tag_groups : false,
      "domainExt" => $this->domain_ext,
      "siteUrl" => $this->site_url,
    ];
  }
}
