<?php

namespace Passle\PassleSync\SyncHandlers\Handlers;

use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\ISyncHandler;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\Content\PostsWordpressContentService;
use Passle\PassleSync\Services\PassleContentService;


class PostHandler extends SyncHandlerBase implements ISyncHandler
{
    private $shortcodeKey = "PostShortcode";

    public function __construct(
        PostsWordpressContentService $wordpress_content_service,
        PassleContentService $passle_content_service)
    {
        parent::__construct($passle_content_service);
        $this->wordpress_content_service = $wordpress_content_service;
    }

    protected function sync_all_impl()
    {
        $passle_posts = $this->passle_content_service->get_stored_passle_posts_from_api();
        $existing_posts = $this->wordpress_content_service->get_items();

        return $this->compare_items($passle_posts, $existing_posts, $this->shortcodeKey, 'post_shortcode');
    }

    protected function sync_one_impl(array $data)
    {
        $existing_post = $this->wordpress_content_service->get_item_by_shortcode($data[$this->shortcodeKey], 'post_shortcode');

        if ($existing_post == null) {
            return $this->sync(null, $data);
        } else {
            return $this->sync($existing_post, $data);
        }
    }

    protected function delete_all_impl()
    {
        $existing_posts = $this->wordpress_content_service->get_items();

        $response = true;
        foreach ($existing_posts as $post) {
            $response &= $this->delete($post);
        }
        return $response;
    }

    protected function delete_one_impl(array $data)
    {
        $existing_post = $this->wordpress_content_service->get_item_by_shortcode($data[$this->shortcodeKey], 'post_shortcode');

        if ($existing_post != null) {
            return $this->delete($existing_post);
        }
    }

    protected function sync(?object $post, array $data)
    {
        // Find if there's an existing post with this shortcode
        // Update it, if so
        $id = 0;
        $existing_post = $this->wordpress_content_service->get_item_by_shortcode($data['PostShortcode'], 'post_shortcode');
        if ($existing_post != null) {
            $id = $existing_post->ID;
        }

        // Update the fields from the new data, using the existing property values as a default
        $post_title = $this->update_property($post, "post_title", $data, "PostTitle");
        $post_content = $this->update_property($post, "post_content", $data, "PostContentHtml");
        $post_date = $this->update_property($post, "post_date", $data, "PublishedDate");
        $post_shortcode = $this->update_property($post, "post_shortcode", $data, "PostShortcode");
        $passle_shortcode = $this->update_property($post, "passle_shortcode", $data, "PassleShortcode");
        $post_authors = $this->update_property($post, "post_authors", $data, fn($x) => implode(", ", Utils::array_select($x['Authors'], "Name")));
        $post_is_repost = $this->update_property($post, "post_is_repost", $data, "IsRepost", false);
        $post_read_time = $this->update_property($post, "post_read_time", $data, "EstimatedReadTimeInSeconds", 0);
        $post_tags = $this->update_property($post, "post_tags", $data, fn($x) => implode(", ", $x['Tags']));
        $post_image = $this->update_property($post, "post_image", $data, "ImageUrl");
        $post_image_html = $this->update_property($post, "post_image_html", $data, "FeaturedItemHTML");
        $post_excerpt = $this->update_property($post, "post_excerpt", $data, "ContentTextSnippet");

        $new_item = [
            'ID'                => $id,
            'post_title'        => $post_title,
            'post_date'         => $post_date,
            'post_type'         => PASSLESYNC_POST_TYPE,
            'post_content'      => $post_content,
            'post_excerpt'      => $post_excerpt,
            'post_status'       => 'publish',
            'comment_status'    => 'closed',
            'meta_input'    => [
                'post_shortcode'    => $post_shortcode,
                'passle_shortcode'  => $passle_shortcode,
                'post_authors'      => $post_authors,
                'post_is_repost'    => $post_is_repost,
                'post_read_time'    => $post_read_time,
                'post_tags'         => $post_tags,
                'post_image'        => $post_image,
                'post_image_html'   => $post_image_html,
            ],
        ];

        $new_id = wp_insert_post($new_item, true);
        if ($new_id != $id)
        {
            $new_item["ID"] = $new_id;
        }

        return $new_item;
    }

    protected function delete(object $post)
    {
        return $this->delete_item($post->ID);
    }
}
