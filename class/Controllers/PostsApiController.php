<?php

namespace Passle\PassleSync\Controllers;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\SyncHandlers\Handlers\PostHandler;
use Passle\PassleSync\Services\Content\PostsWordpressContentService;
use Passle\PassleSync\Services\PassleContentService;

class PostsApiController extends ApiControllerBase implements IApiController
{
    protected $fields = array(
        'Shortcode',
    );
    protected $passle_content_service;
    protected $wordpress_content_service;

    public function __construct(
        PostsWordpressContentService $wordpress_content_service,
        PassleContentService $passle_content_service,
        PostHandler $sync_handler)
    {
        parent::__construct();
        $this->sync_handler = $sync_handler;
        $this->passle_content_service = $passle_content_service;
        $this->wordpress_content_service = $wordpress_content_service;
    }

    public function register_api_routes()
    {
        $this->register_route('/posts', 'GET', 'get_all_items');
        $this->register_route('/posts/api', 'GET', 'get_stored_items_from_api');
        $this->register_route('/posts/api/update', 'GET', 'update_items');
        $this->register_route('/posts/api/sync', 'POST', 'sync_items');
        $this->register_route('/posts/api/sync/progress', 'GET', 'check_sync_items_progress');
        $this->register_route('/post/update', 'POST', 'update_item');
        $this->register_route('/posts/delete', 'POST', 'delete_existing_items');
        $this->register_route('/post/delete', 'POST', 'delete_existing_item');
    }

    public function get_all_items($data)
    {
        return $this->wordpress_content_service->get_items();
    }

    public function get_stored_items_from_api()
    {
        // return $this->passle_content_service->get_stored_passle_posts_from_api();
        return $this->wordpress_content_service->get_or_update_items('passle_posts_from_api', array($this->passle_content_service, 'update_all_passle_posts_from_api'));
    }

    public function update_items()
    {
        return $this->passle_content_service->update_all_passle_posts_from_api();
    }

    public function sync_items($data)
    {
        $items = $data->get_json_params();
        return $this->passle_content_service->sync_all_passle_posts_from_api($items);
    }

    public function check_sync_items_progress()
    {
        return $this->passle_content_service->check_queue_progress();
    }

    public function update_item($data)
    {
        $post_data = $data->get_json_params();

        if (!isset($post_data)) {
            return new \WP_Error('no_data', 'You must include data to create a post', array('status' => 400));
        }

        if (!isset($post_data['PostTitle'])) {
            return new \WP_Error('no_title', 'You must include a post title', array('status' => 400));
        }

        if (!isset($post_data['ContentTextSnippet']) && !isset($post_data['PostContentHtml'])) {
            return new \WP_Error('no_content', 'You must include post content', array('status' => 400));
        }

        return $this->wordpress_content_service->update_item($post_data);
        // TODO: Use this
        // return $this->sync_handler->sync_one($data);
    }

    public function delete_existing_items()
    {
        return $this->sync_handler->delete_all();
    }

    public function delete_existing_item(object $data)
    {
        $post = $this->wordpress_content_service->get_item_by_shortcode($data['PostShortcode']);
        return $this->sync_handler->delete();
    }
}