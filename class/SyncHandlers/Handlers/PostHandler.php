<?php

namespace Passle\PassleSync\SyncHandlers\Handlers;

use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\ISyncHandler;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\WordpressContentService;
use Passle\PassleSync\Services\PassleContentService;


class PostHandler extends SyncHandlerBase implements ISyncHandler
{
    private $shortcodeKey = "PostShortcode";

    public function __construct(
        WordpressContentService $wordpress_content_service,
        PassleContentService $passle_content_service)
    {
        parent::__construct($wordpress_content_service, $passle_content_service);
    }

    protected function create_blank_item()
    {
        return $this->wordpress_content_service->create_new_blank_post();
    }

    protected function sync_all_impl()
    {
        $passle_posts = $this->passle_content_service->get_stored_passle_posts_from_api();
        $existing_posts = $this->wordpress_content_service->get_passle_posts();

        return $this->compare_items($passle_posts, $existing_posts, $shortcodeKey, 'post_shortcode');
    }

    protected function sync_one_impl(array $data)
    {
        $existing_post = $this->wordpress_content_service->get_passle_post_by_shortcode($data[$shortcodeKey]);

        if ($existing_post == null) {
            $new_post = $this->create_blank_item();
            $this->sync($new_post, $data);
        } else {
            $this->sync($existing_post, $data);
        }
    }

    protected function delete_all_impl()
    {
        $existing_posts = $this->wordpress_content_service->get_passle_posts();

        $response = true;
        foreach ($existing_posts as $post) {
            $response |= $this->delete($post);
        }
        return $response;
    }

    protected function delete_one_impl(array $data)
    {
        $existing_post = $this->wordpress_content_service->get_passle_post_by_shortcode($data[$shortcodeKey]);

        if ($existing_post != null) {
            $this->delete($existing_post);
        }
    }

    protected function sync(object $post, array $data)
    {
        $this->wordpress_content_service->update_post_data($post, $data);
    }

    protected function delete(object $post)
    {
        $this->wordpress_content_service->delete_post($post->ID);
    }
}
