<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Utils\UrlFactory;

class ApiService
{
    private $sync_service;
    private $content_service;
    private $api_key;

    public function __construct(SyncService $sync_service, ContentService $content_service)
    {
        $this->sync_service = $sync_service;
        $this->content_service = $content_service;
        $this->api_key = get_option(PASSLESYNC_API_KEY);
    }

    public function register_api_routes()
    {
        // TODO: Validate API Key

        register_rest_route('passlesync/v1', '/sync-all', array(
            'methods' => 'POST',
            'callback' => array($this->sync_service, "sync_all"),
        ));

        register_rest_route('passlesync/v1', '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_passle_posts'),
        ));

        register_rest_route('passlesync/v1', '/post/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_passle_post'),
        ));

        register_rest_route('passlesync/v1', '/post/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_passle_post'),
        ));

        register_rest_route('passlesync/v1', '/posts/api', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_passle_posts_from_api'),
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

    public function get_all_passle_posts($data)
    {
        return $this->content_service->get_passle_posts();
    }

    public function get_passle_post($data)
    {
        $post_data = $data->get_json_params();

        return $this->content_service->get_passle_post($post_data);
    }

    public function update_passle_post($data)
    {
        $post_data = $data->get_json_params();

        if (empty($post_data)) {
            return new WP_Error('no_data', 'You must include data to create a post', array('status' => 400));
        }

        if (empty($post_data['PostTitle'])) {
            return new WP_Error('no_title', 'You must include a post title', array('status' => 400));
        }

        if (empty($post_data['ContentTextSnippet'])) {
            return new WP_Error('no_content', 'You must include post content', array('status' => 400));
        }

        return $this->content_service->update_passle_post($post_data);
    }

    public function get_passle_posts_from_api($data)
    {
        $passle_shortcode = get_option(PASSLESYNC_SHORTCODE);
        $factory = new UrlFactory();
        $url = $factory
            ->path('/posts')
            ->parameters(array(
                'PassleShortcode' => $passle_shortcode,
                'ItemsPerPage' => '100'
            ))
            ->build();

        $responses = $this->get_all_paginated($url);

        $result = Utils::array_select_multiple($responses, 'Posts');

        // foreach ($responses as $response) {
        //     if (isset($response['Posts'])) {
        //         $result = array_merge($result, $response['Posts']);
        //     } else {
        //         $error = new \WP_Error( 'missing_data', 'No Posts in response', array( 'status' => 500 ) );
        //         $error->add_data($url);
        //         return $error;
        //     }
        // }

        return $result;
    }
}
