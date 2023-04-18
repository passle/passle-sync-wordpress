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
  public bool $simulate_remote_hosting;
  public bool $include_passle_posts_on_home_page;
  public bool $include_passle_posts_on_tag_page;
  public string $domain_ext;
  public string $site_url;

  /** @param string[] $passle_shortcodes */
  public function __construct(
    string $passle_api_key,
    string $plugin_api_key,
    array $passle_shortcodes,
    string $post_permalink_template,
    string $person_permalink_template,
    bool $simulate_remote_hosting,
    bool $include_passle_posts_on_home_page,
    bool $include_passle_posts_on_tag_page,
    string $domain_ext,
    string $site_url
  ) {
    $this->passle_api_key = $passle_api_key;
    $this->plugin_api_key = $plugin_api_key;
    $this->passle_shortcodes = $passle_shortcodes;
    $this->post_permalink_template = $post_permalink_template;
    $this->person_permalink_template = $person_permalink_template;
    $this->simulate_remote_hosting = $simulate_remote_hosting;
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
      "postPermalinkTemplate" => $this->post_permalink_template,
      "personPermalinkTemplate" => $this->person_permalink_template,
      "simulateRemoteHosting" => $this->simulate_remote_hosting,
      "includePasslePostsOnHomePage" => isset($this->include_passle_posts_on_home_page) ? $this->include_passle_posts_on_home_page : false,
      "includePasslePostsOnTagPage" => isset($this->include_passle_posts_on_tag_page) ? $this->include_passle_posts_on_tag_page : false,
      "domainExt" => $this->domain_ext,
      "siteUrl" => $this->site_url,
    ];
  }
}
