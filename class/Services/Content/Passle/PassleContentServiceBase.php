<?php

namespace Passle\PassleSync\Services\Content\Passle;

use Exception;
use Passle\PassleSync\Services\OptionsService;
use Passle\PassleSync\Utils\ResourceClassBase;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

abstract class PassleContentServiceBase extends ResourceClassBase
{
  public static function get_cache()
  {
    $cache_storage_key = static::get_resource_instance()->get_cache_storage_key();

    $items = static::get_cached_items($cache_storage_key);

    if (gettype($items) != "array" || count($items) == 0 || reset($items) == null) {
      $items = static::fetch_all();
    }

    return $items;
  }

  public static function overwrite_cache(?array $data)
  {
    $options = OptionsService::get();

    $cache_storage_key = static::get_resource_instance()->get_cache_storage_key();

    if ($data == null) {
      static::clear_cached_items($cache_storage_key);
      return;
    }

    $chunks = array_chunk($data, 50);

    foreach ($chunks as $index => $chunk) {
      
      // update_option will fail if we try to update it with the same value as it's current value
      // so we check to suppress the error log in this case
      $existing_items = get_option("{$cache_storage_key}_{$index}", false);

      if ($existing_items === $chunk) {
        write_log("No need to overwrite Tag groups cache key {$cache_storage_key}_{$index}. Existing value is the same as the new value.", $options->enable_debug_logging);
        continue;
      }

      $success = update_option("{$cache_storage_key}_{$index}", $chunk, false);
      
      if (!$success) {
        $existing_items_json = json_encode($existing_items);
		$chunk_json = json_encode($chunk);
        write_log("Failed to overwrite cache: {$cache_storage_key}_{$index}.  Existing value: { $existing_items_json }. New value: { $chunk_json }", $options->enable_debug_logging);
      }
    }
  }

  public static function update_cache(array $data)
  {
    $cache_storage_key = static::get_resource_instance()->get_cache_storage_key();
    $shortcode_prop = static::get_resource_instance()->get_shortcode_name();
    $existing_items = static::get_cached_items($cache_storage_key);

    foreach ($data as $item) {
      $exists = false;
      foreach ($existing_items as $i => $existing_item) {
        if ($item[$shortcode_prop] == $existing_item[$shortcode_prop]) {
          $existing_items[$i] = $item;
          $exists = true;
        }
      }
      if (!$exists) {
        array_push($existing_items, $item);
      }
    }

    static::overwrite_cache($existing_items);
  }

  public static function fetch_all()
  {
    $options = OptionsService::get();

    $passle_shortcodes = $options->passle_shortcodes;

    /** @var array[] $results */
    $results = array_map(fn ($passle_shortcode) => static::fetch_all_by_passle($passle_shortcode), $passle_shortcodes);

    if (in_array(true, array_map(function ($r) {
      return is_wp_error($r);
    }, $results))) {
      dd($results);
      throw new Exception("Failed to get data from the API", 500);
    }

    $result = array_merge(...$results);

    if (!is_null($result)) {
      // Set the default sync state to unsynced
      array_walk($result, fn (&$i) => $i["SyncState"] = 0);

      static::overwrite_cache($result);
      return $result;
    } else {
      static::overwrite_cache(array());
      return array();
    }
  }

  public static function fetch_all_by_passle(string $passle_shortcode)
  {
    $resource = static::get_resource_instance();
    $options = OptionsService::get();

    $path = "passlesync/{$resource->name_plural}";
    
    $parameters = array(
      "PassleShortcode" => $passle_shortcode,
      "ItemsPerPage" => "100",
      "IncludeTagGroups" => "true"
    );

    $url = (new UrlFactory())
        ->path($path)
        ->parameters($parameters)
        ->build();

    $responses = static::get_all_paginated($url);

    if (is_null($responses) || in_array(null, $responses)) {
      return array();
    }

    $result = Utils::array_select_multiple($responses, ucfirst($resource->name_plural));

    return $result;
  }

  public static function fetch_by_shortcode(string $entity_shortcode)
  {
    return static::fetch_multiple_by_shortcode(array($entity_shortcode));
  }

  public static function fetch_multiple_by_shortcode(array $entity_shortcodes)
  {
    $resource = static::get_resource_instance();
    $options = OptionsService::get();

    $params = [
      $resource->get_api_parameter_shortcode_name() => join(",", $entity_shortcodes),
      "IncludeTagGroups" => "true"
    ];

    $factory = new UrlFactory();
    $url = $factory
      ->path("passlesync/{$resource->name_plural}")
      ->parameters($params)
      ->build();

    $response = static::get($url);
    if ($response) {
      try {
        $data = $response[ucfirst($resource->name_plural)];
      }
      catch(Error $e) {
        write_log('Failed to parse response from the API ' . json_encode(['error' => $e->getMessage()]), $options->enable_debug_logging);
        $data = array();
      }
    } else {
      $data = array();
    }
    
    static::update_cache($data);

    return $data;
  }

  public static function get_all_paginated(string $url, int $page_number = 1)
  {
    $result = [];
    $next_url = static::get_next_url($url, $page_number);

    while ($next_url !== null) {
      $response = static::get($next_url);
      array_push($result, $response);

      $more_data_available = false;
      if (isset($response['TotalCount']) && isset($response['PageSize']) && isset($response['PageNumber'])) {
        $more_data_available = $response['TotalCount'] > ($response['PageSize'] * $response['PageNumber']);
      }

      if ($more_data_available) {
        $page_number += 1;
        $next_url = static::get_next_url($url, $page_number);
      } else {
        $next_url = null;
      }
    }

    return $result;
  }

  public static function get_next_url(string $url, int $page_number)
  {
    $parsed_url = wp_parse_url($url);
    wp_parse_str($parsed_url['query'], $query);

    $query['PageNumber'] = strval($page_number);

    $next_url = (new UrlFactory())
      ->protocol($parsed_url['scheme'])
      ->root($parsed_url['host'])
      ->path($parsed_url['path'])
      ->parameters($query)
      ->build();

    return $next_url;
  }

  public static function get(string $url)
  {
    $options = OptionsService::get();

    $site_url = home_url();
    $domain = preg_replace('/^https?:\/\//', '', $site_url);
    $use_https = strpos($site_url, 'https') === 0;

    $headers = [
      "apiKey" => $options->passle_api_key,
    ];

    if ($options->simulate_remote_hosting) {
      $headers = array_merge($headers, [
        "X-PassleSimulateRemoteHosting" => "true",
        "X-PassleRemoteHostingUseHttps" => $use_https,
        "X-PassleRemoteHostingCustomDomain" => $domain,
        "X-PassleRemoteHostingPostPath" => $options->post_permalink_template,
        "X-PassleRemoteHostingProfilePath" => $options->person_permalink_template,
      ]);
    }

    $request = wp_remote_get($url, [
      'sslverify' => false,
      'headers' => $headers,
    ]);

    $body = wp_remote_retrieve_body($request);
    $data = json_decode($body, true);

    return $data;
  }

  public static function get_cached_items(string $cache_storage_key)
  {
    $items = array();
    $index = 0;
    while (($chunk = get_option("{$cache_storage_key}_{$index}", false)) !== false) {
      if (!is_array($chunk)) {
        break; // Prevents corruption if an unexpected value is encountered
      }
      $items = array_merge($items, $chunk);
      $index++;
    }

    return $items;
  }

  public static function clear_cached_items(string $cache_storage_key)
  {
    $index = 0;
    while (($chunk = get_option("{$cache_storage_key}_{$index}", false)) !== false) {
      if ($chunk === array()) {
        break;  // Prevents infinite loop when resetting to empty arrays
      }
      delete_option("{$cache_storage_key}_{$index}", array(), false);
      $index++;
    }
  }
}
