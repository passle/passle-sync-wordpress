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
        $this->register_route('/posts/update', 'POST', 'update_items');
        $this->register_route('/post/update', 'POST', 'update_item');
        $this->register_route('/posts/delete', 'POST', 'delete_existing_items');
        $this->register_route('/posts/refresh', 'GET', 'refresh_items');
    }

    public function get_all_items($data)
    {
        $wp_posts = $this->wordpress_content_service->get_items();
        $api_posts = $this->passle_content_service->get_stored_passle_posts_from_api();

        $wp_post_shortcodes = array_map(fn ($p) => $p->post_shortcode, $wp_posts);
        $unsynced_api_posts = array_filter($api_posts, fn ($p) => !in_array($p['PostShortcode'], $wp_post_shortcodes));

        return array(
            "syncedPosts" => $wp_posts,
            "unsyncedPosts" => array_values($unsynced_api_posts),   // Return the values as this is an associative array
        );
    }

    public function update_items()
    {  
        return $this->sync_handler->sync_all();
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

        // If the post isn't for this Passle, ignore it
        // This is useful to prevent reposts being added when a post is saved
        $passle_shortcodes = get_option(PASSLESYNC_SHORTCODE);
        if (!in_array($post_data['PassleShortcode'], $passle_shortcodes))
        {
            return new \WP_Error('wrong_passle', "Passle shortcode (" . $post_data['PassleShortcode'] . ") is not in list (" . join(', ', $passle_shortcodes) . ")", array('status' => 400));
        }

        return $this->sync_handler->sync_one($post_data);
    }

    public function delete_existing_items()
    {
        return $this->sync_handler->delete_all();
    }

    public function refresh_items()
    {
        $wp_posts = $this->wordpress_content_service->get_items();
        $api_posts = $this->passle_content_service->update_all_passle_posts_from_api();

        $wp_post_shortcodes = array_map(fn ($p) => $p->post_shortcode, $wp_posts);
        $unsynced_api_posts = array_filter($api_posts, fn ($p) => !in_array($p['PostShortcode'], $wp_post_shortcodes));
        
        return array_values($unsynced_api_posts);
    }
}