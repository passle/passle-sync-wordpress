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
    private $queue;
    protected $fields = array();
    public $items_being_synced = 0;

    public function __construct(
        WordpressContentService $wordpress_content_service)
    {
        $this->wordpress_content_service = $wordpress_content_service;
        $this->plugin_api_key = get_option(PASSLESYNC_PLUGIN_API_KEY);
        $this->api_key = get_option(PASSLESYNC_CLIENT_API_KEY);
        $this->queue = new \SplQueue();
    }

    public function verify_header_api_key($request)
    {
        if ($request->get_header('APIKey') == $this->plugin_api_key) {
            return true;
        }
        return false;
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

    public function register_api_settings_routes()
    {
        $this->register_route('/settings/update', 'POST', 'update_api_settings');
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

        if (in_array(true, array_map(function($r) {
            return is_wp_error($r);
        }, $results))) {
            return new \WP_Error('no_response', 'Failed to get data from the API', array('status' => 500));
        }

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

        if (in_array(null, $responses)) {
            return new \WP_Error('no_response', 'Failed to get data from the API', array('status' => 500));
        }

        $result = Utils::array_select_multiple($responses, $response_key);

        return $result;
    }

    public function queue_all_items_for_update_from_api(array $items, string $passle_shortcode, string $response_key, string $path, $callback)
    {
        // Get more detailed info for each item
        foreach ($items as $item) {
            $this->queue->enqueue($item['PostShortcode']);
        }

        update_option('item_queue_length', $this->queue->count(), false);
        $this->start_queue_handler($callback);
        update_option('item_queue_length', 0, false);
        update_option('item_queue_remaining', 0, false);
    }

    public function start_queue_handler($callback)
    {
        if ($this->queue->count() > 0) {
            update_option('item_queue_remaining', $this->queue->count(), false);
            $this->process_queue($this->queue->dequeue(), $callback);
        }
    }

    public function process_queue($shortcode, $callback)
    {
        $factory = new UrlFactory();
        $url = $factory
            ->path('/posts/'.$shortcode)
            ->build();

        $data = $this->get($url);
        $callback($data);
        $this->start_queue_handler($callback);
    }

    public function check_queue_progress() 
    {
        // update_option('item_queue_length', 0, false);
        // update_option('item_queue_remaining', 0, false);
        $total = get_option('item_queue_length');
        $remaining = get_option('item_queue_remaining');

        return array(
            'total'     => $total ? intval($total) : 0,
            'remaining' => $remaining ? intval($remaining) : 0
        );
    }

    public function update_api_settings($data)
    {
        $json_params = $data->get_json_params();

        if (!isset($json_params)) {
            return new \WP_Error('no_data', 'You must include data to update settings', array('status' => 400));
        }

        update_option(PASSLESYNC_PLUGIN_API_KEY, $json_params['pluginApiKey'], true);
        update_option(PASSLESYNC_CLIENT_API_KEY, $json_params['clientApiKey'], true);
        update_option(PASSLESYNC_SHORTCODE, $json_params['passleShortcodes'], true);
        return true;
    }
}