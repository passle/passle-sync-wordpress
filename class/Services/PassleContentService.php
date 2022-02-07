<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\Api\ApiServiceBase;

class PassleContentService
{
    private $api_key;
    private $api_service_base;
    private $wordpress_content_service;

    public function __construct(ApiServiceBase $api_service_base, WordpressContentService $wordpress_content_service)
    {
        $this->api_key = get_option(PASSLESYNC_CLIENT_API_KEY);
        $this->api_service_base = $api_service_base;
        $this->wordpress_content_service = $wordpress_content_service;
    }

    public function get_stored_passle_posts_from_api()
    {
        return $this->wordpress_content_service->get_or_update_items('passle_posts_from_api', array($this, 'update_all_passle_posts_from_api'));
    }

    public function update_all_passle_posts_from_api()
    {
        return $this->api_service_base->get_all_items_from_api('passle_posts_from_api', 'Posts', '/posts');
    }

    public function sync_all_passle_posts_from_api($data)
    {
        return $this->api_service_base->queue_all_items_for_update_from_api($data, 'passle_posts_from_api', 'Posts', '/posts', array($this->wordpress_content_service, 'update_passle_post'));
    }

    public function get_stored_passle_authors_from_api()
    {
        return $this->wordpress_content_service->get_or_update_items('passle_authors_from_api', array($this, 'update_all_passle_authors_from_api'));
    }

    public function update_all_passle_authors_from_api()
    {
        return $this->api_service_base->get_all_items_from_api('passle_authors_from_api', 'People', '/people');
    }
}
