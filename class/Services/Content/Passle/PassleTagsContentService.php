<?php

namespace Passle\PassleSync\Services\Content\Passle;

use Exception;
use Passle\PassleSync\Services\OptionsService;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

class PassleTagsContentService
{
  public static function fetch_all()
  {
    $passle_shortcodes = OptionsService::get()->passle_shortcodes;

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
      return $result;
    } else {
      return array();
    }
  }

  public static function fetch_all_by_passle(string $passle_shortcode)
  {
    $url = (new UrlFactory())
      ->path("/tags/".$passle_shortcode)
      ->build();

    $responses = static::get_all_paginated($url);

    if (is_null($responses) || in_array(null, $responses)) {
      return array();
    }

    $result = Utils::array_select_multiple($responses, ucfirst("tags"));

    return $result;
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

  private static function get_next_url(string $url, int $page_number)
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

  private static function get(string $url)
  {
    $passle_api_key = OptionsService::get()->passle_api_key;

    $request = wp_remote_get($url, [
      'sslverify' => false,
      'headers' => [
        "apiKey" => $passle_api_key,
        "X-PassleSimulateRemoteHosting" => "true",
      ]
    ]);

    $body = wp_remote_retrieve_body($request);
    $data = json_decode($body, true);

    return $data;
  }
}
