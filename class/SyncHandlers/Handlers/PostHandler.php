<?php

namespace Passle\PassleSync\SyncHandlers\Handlers;

use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\ISyncHandler;
use Passle\PassleSync\Utils\UrlFactory;

class PostHandler extends SyncHandlerBase implements ISyncHandler
{
    protected function get_existing_content()
    {
    }

    protected function get_passle_content($passle_shortcode)
    {
        $factory = new UrlFactory();
        $url = $factory
            ->path('posts')
            ->parameters(array(
                'PassleShortcode' => $passle_shortcode,
                'ItemsPerPage' => '100'
            ))
            ->build();

        $responses = $this->api_service->get_all_paginated($url);
        $result = [];

        foreach ($responses as $response) {
            $result = array_merge($result, $response['Posts']);
        }

        return $result;
    }

    protected function sync_all_impl()
    {
    }

    protected function sync_one_impl()
    {
    }

    protected function delete_one_impl()
    {
    }

    protected function sync()
    {
    }

    protected function delete()
    {
    }
}
