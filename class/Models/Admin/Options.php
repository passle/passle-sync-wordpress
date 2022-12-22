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
  public bool $include_passle_posts_on_home_page;
  public bool $include_passle_posts_on_tag_page;
  public bool $simulate_remote_hosting;
  public bool $use_https;
  public string $custom_domain;
  public string $passle_permalink_prefix;
  public string $domain_ext;
  public string $site_url;

  /** @param string[] $passle_shortcodes */
  public function __construct(
    string $passle_api_key,
    string $plugin_api_key,
    array $passle_shortcodes,
    string $post_permalink_prefix,
    string $person_permalink_prefix,
    bool $include_passle_posts_on_home_page,
    bool $include_passle_posts_on_tag_page,
    bool $simulate_remote_hosting,
    bool $use_https,
    string $custom_domain,
    string $passle_permalink_prefix,
    string $domain_ext,
    string $site_url
  ) {
    $this->passle_api_key = $passle_api_key;
    $this->plugin_api_key = $plugin_api_key;
    $this->passle_shortcodes = $passle_shortcodes;
    $this->post_permalink_prefix = $post_permalink_prefix;
    $this->person_permalink_prefix = $person_permalink_prefix;
    $this->include_passle_posts_on_home_page = $include_passle_posts_on_home_page;
    $this->include_passle_posts_on_tag_page = $include_passle_posts_on_tag_page;
    $this->simulate_remote_hosting = $simulate_remote_hosting;
    $this->use_https = $use_https;
    $this->custom_domain = $custom_domain;
    $this->passle_permalink_prefix = $passle_permalink_prefix;
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
      "includePasslePostsOnHomePage" => isset($this->include_passle_posts_on_home_page) ? $this->include_passle_posts_on_home_page : false,
      "includePasslePostsOnTagPage" => isset($this->include_passle_posts_on_tag_page) ? $this->include_passle_posts_on_tag_page : false,
      "simulateRemoteHosting" => isset($this->simulate_remote_hosting) ? $this->simulate_remote_hosting : false,
      "useHttps" => isset($this->use_https) ? $this->use_https : false,
      "customDomain" => $this->custom_domain,
      "passlePermalinkPrefix" => $this->passle_permalink_prefix,
      "domainExt" => $this->domain_ext,
      "siteUrl" => $this->site_url,
    ];
  }
}
