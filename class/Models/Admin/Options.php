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
  public string $domain_ext;

  /** @param string[] $passle_shortcodes */
  public function __construct(
    string $passle_api_key,
    string $plugin_api_key,
    array $passle_shortcodes,
    string $post_permalink_prefix,
    string $person_permalink_prefix,
    string $domain_ext
  ) {
    $this->passle_api_key = $passle_api_key;
    $this->plugin_api_key = $plugin_api_key;
    $this->passle_shortcodes = $passle_shortcodes;
    $this->post_permalink_prefix = $post_permalink_prefix;
    $this->person_permalink_prefix = $person_permalink_prefix;
    $this->domain_ext = $domain_ext;
  }

  public function jsonSerialize()
  {
    return [
      "passleApiKey" => $this->passle_api_key,
      "pluginApiKey" => $this->plugin_api_key,
      "passleShortcodes" => $this->passle_shortcodes,
      "postPermalinkPrefix" => $this->post_permalink_prefix,
      "personPermalinkPrefix" => $this->person_permalink_prefix,
      "domainExt" => $this->domain_ext,
    ];
  }
}
