<?php

namespace Passle\PassleSync\Services\Api;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\SyncHandlers\Handlers\PostHandler;
use Passle\PassleSync\Services\WordpressContentService;

class ApiServiceBase
{
    protected $wordpress_content_service;
    protected $sync_handler;
    private $plugin_api_key;
    private $api_key;
    protected $fields = array();

    public function __construct(
        WordpressContentService $wordpress_content_service)
    {
        $this->wordpress_content_service = $wordpress_content_service;
        $this->plugin_api_key = get_option(PASSLESYNC_PLUGIN_API_KEY);
        $this->api_key = get_option(PASSLESYNC_CLIENT_API_KEY);
    }

    public function verify_header_api_key($request)
    {
        if ($request->get_header('APIKey') == $this->plugin_api_key) {
            return true;
        }
        return true;
    }

    public function register_route(string $path, string $method, string $func_name)
    {
        register_rest_route(PASSLESYNC_REST_API_BASE, $path, array(
            'methods' => $method,
            'callback' => array($this, $func_name),
            'validate_callback' => function($request) {
                return $this->verify_header_api_key($request);
            },
            'permission_callback' => '__return_true',
        ));
    }    

    public function get(string $url)
    {
        $request = wp_remote_get($url, array(
            'sslverify' => false,
            'headers' => array(
                "apiKey" => $this->api_key
            )
        ));

        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);

        return $data;
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

    public function get_all_items_from_api(string $storage_key, string $response_key, string $path)
    {
        $passle_shortcodes = get_option(PASSLESYNC_SHORTCODE);

        $results = array_map(function($passle_shortcode) use ($response_key, $path) {
           return $this->get_items_for_passle_from_api($passle_shortcode, $response_key, $path);
        }, $passle_shortcodes);

        $result = array_merge(...$results);
        
        usort($result, function($a, $b) {
            return strcmp($b['PublishedDate'], $a['PublishedDate']);
        });

        update_option($storage_key, $result, false);

        return $result;
    }

    public function get_items_for_passle_from_api(string $passle_shortcode, string $response_key, string $path)
    {
        $factory = new UrlFactory();
        $url = $factory
            ->path($path)
            ->parameters(array(
                'PassleShortcode' => $passle_shortcode,
                'ItemsPerPage' => '100'
            ))
            ->build();

        $responses = $this->get_all_paginated($url);

        $result = Utils::array_select_multiple($responses, $response_key);

        return $result;
    }
}