<?php

namespace Passle\PassleSync\Services;

use Exception;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\Content\PeopleWordpressContentService;
use Passle\PassleSync\Services\Content\PostsWordpressContentService;

class PassleContentService
{
  private $passle_api_key;
  private $people_wordpress_content_service;
  private $posts_wordpress_content_service;

  public function __construct(
    PeopleWordpressContentService $people_wordpress_content_service,
    PostsWordpressContentService $posts_wordpress_content_service
  ) {
    $options = OptionsService::get();
    $this->passle_api_key = $options->passle_api_key;
    $this->people_wordpress_content_service = $people_wordpress_content_service;
    $this->posts_wordpress_content_service = $posts_wordpress_content_service;
  }

  public function get_stored_passle_posts_from_api()
  {
    return $this->posts_wordpress_content_service->get_or_update_items('passle_posts_from_api', [$this, 'update_all_passle_posts_from_api']);
  }

  public function get_stored_passle_authors_from_api()
  {
    return $this->people_wordpress_content_service->get_or_update_items('passle_authors_from_api', [$this, 'update_all_passle_authors_from_api']);
  }

  public function update_all_passle_posts_from_api()
  {
    return $this->get_all_items_from_api('passle_posts_from_api', 'Posts', 'posts');
  }

  public function update_all_passle_authors_from_api()
  {
    return $this->get_all_items_from_api('passle_authors_from_api', 'People', 'people');
  }

  public function get_all_items_from_api(string $storage_key, string $response_key, string $path)
  {
    $passle_shortcodes = OptionsService::get()->passle_shortcodes;

    /** @var array[] $results */
    $results = array_map(function ($passle_shortcode) use ($response_key, $path) {
      return $this->get_items_from_api_for_passle($passle_shortcode, $response_key, $path);
    }, $passle_shortcodes);

    if (in_array(true, array_map(function ($r) {
      return is_wp_error($r);
    }, $results))) {
      throw new Exception('Failed to get data from the API', 500);
    }

    $result = array_merge(...$results);

    // Set the default sync state to unsynced
    array_walk($result, fn (&$i) => $i['SyncState'] = 0);

    usort($result, function ($a, $b) {
      return strcmp($b['PublishedDate'], $a['PublishedDate']);
    });

    update_option($storage_key, $result, false);

    return $result;
  }

  public function get_items_from_api_for_passle(string $passle_shortcode, string $response_key, string $path)
  {
    $factory = new UrlFactory();
    $url = $factory
      ->path("/passlesync/" . $path)
      ->parameters([
        'PassleShortcode' => $passle_shortcode,
        'ItemsPerPage' => '100'
      ])
      ->build();

    $responses = $this->get_all_paginated($url);

    if (in_array(null, $responses)) {
      throw new Exception('Failed to get data from the API', 500);
    }

    $result = Utils::array_select_multiple($responses, $response_key);

    return $result;
  }

  public function get_single_from_api(string $passle_shortcode, string $item_shortcode, string $path, string $response_key)
  {
    $params = [
      'PassleShortcode' => $passle_shortcode
    ];
    if ($path == "posts") {
      $params["PostShortcode"] = $item_shortcode;
    }
    if ($path == "people") {
      $params["PersonShortcode"] = $item_shortcode;
    }

    $factory = new UrlFactory();
    $url = $factory
      ->path("/passlesync/" . $path)
      ->parameters($params)
      ->build();

    $response = $this->get($url);
    return $response[$response_key];
  }

  /** @param array<string> $post_shortcodes */
  public function get_posts(array $post_shortcodes)
  {
    $params = [
      "PostShortcode" => join(",", $post_shortcodes)
    ];

    $factory = new UrlFactory();
    $url = $factory
      ->path("/passlesync/posts")
      ->parameters($params)
      ->build();

    $response = $this->get($url);
    return $response["Posts"];
  }

  /** @param array<string> $author_shortcodes */
  public function get_people(array $author_shortcodes)
  {
    $params = [
      "PersonShortcode" => join(",", $author_shortcodes)
    ];

    $factory = new UrlFactory();
    $url = $factory
      ->path("/passlesync/people")
      ->parameters($params)
      ->build();

    $response = $this->get($url);
    return $response["People"];
  }

  public function get_all_paginated(string $url, int $page_number = 1)
  {
    $result = [];
    $next_url = $this->get_next_url($url, $page_number);

    while ($next_url !== null) {
      $response = $this->get($next_url);
      array_push($result, $response);

      $more_data_available = false;
      if (isset($response['TotalCount']) && isset($response['PageSize']) && isset($response['PageNumber'])) {
        $more_data_available = $response['TotalCount'] > ($response['PageSize'] * $response['PageNumber']);
      }

      if ($more_data_available) {
        $page_number += 1;
        $next_url = $this->get_next_url($url, $page_number);
      } else {
        $next_url = null;
      }
    }

    return $result;
  }

  private function get_next_url(string $url, int $page_number)
  {
    $parsed_url = wp_parse_url($url);
    wp_parse_str($parsed_url['query'], $query);

    $query['PageNumber'] = strval($page_number);

    $factory = new UrlFactory();
    $next_url = $factory
      ->protocol($parsed_url['scheme'])
      ->root($parsed_url['host'])
      ->path($parsed_url['path'])
      ->parameters($query)
      ->build();

    return $next_url;
  }

  public function get(string $url)
  {
    $request = wp_remote_get($url, [
      'sslverify' => false,
      'headers' => [
        "apiKey" => $this->passle_api_key
      ]
    ]);

    $body = wp_remote_retrieve_body($request);
    $data = json_decode($body, true);

    return $data;
  }
}
