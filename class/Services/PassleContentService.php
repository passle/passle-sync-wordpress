<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\Content\PeopleWordpressContentService;
use Passle\PassleSync\Services\Content\PostsWordpressContentService;

class PassleContentService
{
    private $api_key;
    private $people_wordpress_content_service;
    private $posts_wordpress_content_service;

    public function __construct(
        PeopleWordpressContentService $people_wordpress_content_service,
        PostsWordpressContentService $posts_wordpress_content_service
    )
    {
        $this->api_key = get_option(PASSLESYNC_CLIENT_API_KEY);
        $this->people_wordpress_content_service = $people_wordpress_content_service;
        $this->posts_wordpress_content_service = $posts_wordpress_content_service;
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

    // public function get_stored_passle_posts_from_api()
    // {
    //     return $this->posts_wordpress_content_service->get_or_update_items('passle_posts_from_api', array($this, 'update_all_passle_posts_from_api'));
    // }

    public function update_all_passle_posts_from_api()
    {
        return $this->get_all_items_from_api('passle_posts_from_api', 'Posts', '/posts');
    }

    public function get_stored_passle_authors_from_api()
    {
        return $this->people_wordpress_content_service->get_or_update_items('passle_authors_from_api', array($this, 'update_all_passle_authors_from_api'));
    }

    public function update_all_passle_authors_from_api()
    {
        return $this->get_all_items_from_api('passle_authors_from_api', 'People', '/people');
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

    public function sync_all_passle_posts_from_api(array $items)
    {
        update_option('item_queue_length', count($items), false);

        try {
            foreach ($items as $index=>$item) {
                $shortcode = $item['PostShortcode'];
    
                $factory = new UrlFactory();
                $url = $factory
                    ->path("/posts/{$shortcode}")
                    ->build();
    
                $data = $this->get($url);
                $this->posts_wordpress_content_service->update_item($data);
                update_option('item_queue_done', $index + 1, false);
            }
    
            update_option('item_queue_length', 0, false);
            update_option('item_queue_done', 0, false);
        } catch (Exception $e) {
            update_option('item_queue_length', 0, false);
            update_option('item_queue_done', 0, false);
            
            return new \WP_Error('sync_error', $e, array('status' => 500));
        }
    }

    public function check_queue_progress() 
    {
    //     update_option('item_queue_length', 0, false);
    //     update_option('item_queue_done', 0, false);

        $total = get_option('item_queue_length');
        $done = get_option('item_queue_done');

        return array(
            'total'     => $total ? intval($total) : 0,
            'done'      => $done ? intval($done) : 0
        );
    }
}
