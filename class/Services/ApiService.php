<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Utils\UrlFactory;

class ApiService
{
    private $sync_service;
    private $api_key;

    public function __construct(SyncService $sync_service)
    {
        $this->sync_service = $sync_service;
        $this->api_key = get_option(PASSLESYNC_API_KEY);
    }

    public function register_api_routes()
    {
        register_rest_route('passlesync/v1', '/sync-all', array(
            'methods' => 'POST',
            'callback' => array($this->sync_service, "sync_all"),
        ));
    }

    public function get($url)
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

    public function get_all_paginated($url, $page_number = 1)
    {
        $result = [];
        $next_url = $this->get_next_url($url, $page_number);

        while ($next_url !== null) {
            $response = $this->get($next_url);
            array_push($result, $response);

            $more_data_available = $response['TotalCount'] > ($response['PageSize'] * $response['PageNumber']);
            if ($more_data_available) {
                $page_number += 1;
                $next_url = $this->get_next_url($url, $page_number);
            } else {
                $next_url = null;
            }
        }

        return $result;
    }

    private function get_next_url($url, $page_number)
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
}
