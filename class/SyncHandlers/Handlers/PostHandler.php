<?php

namespace Passle\PassleSync\SyncHandlers\Handlers;

use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\ISyncHandler;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

class PostHandler extends SyncHandlerBase implements ISyncHandler
{
    private $fields = array(
        'PostShortcode',
    );

    protected function get_existing_content()
    {
        $posts = get_posts(array(
            'numberposts'   => -1,
            'post_type'     => [PASSLESYNC_POST_TYPE],
        ));

        return $posts;
    }

    protected function get_passle_content(string $passle_shortcode)
    {
    }

    protected function sync_all_impl()
    {
        $passle_shortcodes = Utils::array_select($this->passle_content, "PostShortcode");

        $existing_shortcodes = [];
        $existing_posts_by_shortcode = [];
        $existing_post_shortcodes_by_id = [];

        foreach ($this->existing_content as $post) {
            $post_shortcode = get_metadata('post', $post->ID, 'PASSLESYNC_PostShortcode', true);

            array_push($existing_shortcodes, $post_shortcode);
            $existing_posts_by_shortcode[$post_shortcode] = $post;
            $existing_post_shortcodes_by_id[$post->ID] = $post_shortcode;
        }

        $all_shortcodes = array_unique(array_merge($passle_shortcodes, $existing_shortcodes));

        $shortcodes_to_add = array_filter($passle_shortcodes, fn ($shortcode) => !in_array($shortcode, $existing_shortcodes));
        $shortcodes_to_remove = array_filter($existing_shortcodes, fn ($shortcode) => !in_array($shortcode, $passle_shortcodes));
        $shortcodes_to_update = array_filter($all_shortcodes, fn ($shortcode) => !in_array($shortcode, $shortcodes_to_add) && !in_array($shortcode, $shortcodes_to_remove));

        // Add posts
        $posts_to_add = array_filter($this->passle_content, fn ($post) => in_array($post['PostShortcode'], $shortcodes_to_add));
        foreach ($posts_to_add as $post) {
            $new_post = array(
                'post_type' => PASSLESYNC_POST_TYPE,
                'post_status'   => 'publish',
                'post_author'   => 1
            );

            $post_id = wp_insert_post($new_post);
            $this->sync($post_id, $post);
        }

        // Update posts
        $posts_to_update = array_filter($this->passle_content, fn ($post) => in_array($post['PostShortcode'], $shortcodes_to_update));
        foreach ($posts_to_update as $post) {
            $existing_post = $existing_posts_by_shortcode[$post['PostShortcode']];
            $post_id = $existing_post->ID;
            $this->sync($post_id, $post);
        }

        // Remove posts
        $posts_to_remove = array_filter($this->existing_content, fn ($post) => in_array($existing_post_shortcodes_by_id[$post->ID], $shortcodes_to_remove));
        foreach ($posts_to_remove as $post) {
            $post_id = $post->ID;
            $this->delete($post_id);
        }

        return;
    }

    protected function sync_one_impl(array $data)
    {
    }

    protected function delete_one_impl(array $data)
    {
    }

    protected function sync(int $post_id, array $data)
    {
        $post = array(
            'ID' => $post_id,
            'post_title' => wp_strip_all_tags($data['PostTitle']),
            'post_content' => array_key_exists('PostContentHTML', $data) ? $data['PostContentHTML'] : '',
            'post_date' => $data['PublishedDate']
        );

        wp_update_post($post);

        foreach ($this->fields as $field) {
            add_metadata('post', $post_id, "PASSLESYNC_{$field}", $data[$field]);
        }
    }

    protected function delete(int $post_id)
    {
        wp_delete_post($post_id, true);
    }
}
