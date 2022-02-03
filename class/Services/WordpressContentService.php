<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

class WordpressContentService
{
    public function get_passle_posts()
    {
        $posts = get_posts(array(
            'numberposts'   => -1,
            'post_type'     => array(PASSLESYNC_POST_TYPE),
        ));

        if (empty($posts)) {
            return array();
        }

        array_walk($posts, function($p) {
            $p = $this->apply_meta_data_to_post($p);
        });

        return $posts;
    }

    public function get_passle_post_by_shortcode(string $post_shortcode)
    {
        $posts = $this->get_passle_posts();

        $matching_posts = array_filter($posts, function ($p) use ($data) {
            return $p->post_shortcode === $post_shortcode;
        });

        if (count($matching_posts) > 0) {
            $post = reset($matching_posts);
            return $post;
        } else {
            return null;
        }
    }

    public function update_passle_post(array $data)
    {
        // If the post isn't for this Passle, ignore it
        // This is useful to prevent reposts being added when a post is saved
        $passle_shortcodes = get_option(PASSLESYNC_SHORTCODE);
        if (!in_array($data['PassleShortcode'], $passle_shortcodes))
        {
            return "Passle shortcode (" . $data['PassleShortcode'] . ") is not in list (" . join(', ', $passle_shortcodes) . ")";
        }

        // Find if there's an existing post with this shortcode
        // Update it, if so
        $id = 0;
        $existing_post = $this->get_passle_post_by_shortcode($data['PostShortcode']);
        if ($existing_post != null) {
            $id = $existing_post->ID;
        }

        // TODO: Consider making this only update fields that are set in $data, and retaining existing values otherwise
        $post_title = isset($data['PostTitle']) ? $data['PostTitle'] : '';
        $post_date = isset($data['PublishedDate']) ? $data['PublishedDate'] : '';
        $post_content = isset($data['PostContentHtml']) ? $data['PostContentHtml'] : '';
        $post_shortcode = isset($data['PostShortcode']) ? $data['PostShortcode'] : '';
        $passle_shortcode = isset($data['PassleShortcode']) ? $data['PassleShortcode'] : '';
        $post_authors = isset($data['Authors']) ? implode(", ", Utils::array_select($data['Authors'], "Name")) : '';
        $post_is_repost = isset($data['IsRepost']) ? $data['IsRepost'] : false;
        $post_read_time = isset($data['EstimatedReadTimeInSeconds']) ? $data['EstimatedReadTimeInSeconds'] : 0;
        $post_tags = isset($data['Tags']) ? implode(", ", $data['Tags']) : '';
        $post_image = isset($data['ImageUrl']) ? $data['ImageUrl'] : '';
        $post_preview = isset($data['ContentTextSnippet']) ? $data['ContentTextSnippet'] : '';

        $new_post = array(
            'ID'                => $id,
            'post_title'        => $post_title,
            'post_date'         => $post_date,
            'post_type'         => PASSLESYNC_POST_TYPE,
            'post_content'      => $post_content,
            'post_status'       => 'publish',
            'comment_status'    => 'closed',
            'meta_input'    => array(
                'post_shortcode'    => $post_shortcode,
                'passle_shortcode'  => $passle_shortcode,
                'post_authors'      => $post_authors,
                'post_is_repost'    => $post_is_repost,
                'post_read_time'    => $post_read_time,
                'post_tags'         => $post_tags,
                'post_image'        => $post_image,
                'post_preview'      => $post_preview,
            )
        );

        $pid = wp_insert_post($new_post, true);
        if ($pid != $id)
        {
            $new_post->ID = $pid;
        }

        return $new_post;
    }

    public function apply_meta_data_to_post(object $post)
    {    
        $meta = get_post_meta($post->ID);
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'post_shortcode', "");
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'passle_shortcode', "default");
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'post_authors', "");
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'post_is_repost', "");
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'post_read_time', 0);
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'post_tags', "");
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'post_image', "");
        $post = $this->apply_individual_meta_data_to_post($post, $meta, 'post_preview', "");
        return $post;
    }

    public function apply_individual_meta_data_to_post(object $post, array $meta, string $propName, $default)
    {
        if (!empty($meta[$propName])) {
            $post->{$propName} = $meta[$propName][0];
        } else {
            $post->{$propName} = $default;
        }
        return $post;
    }

    public function create_new_blank_post()
    {
        $new_post = array(
            'post_type'     => PASSLESYNC_POST_TYPE,
            'post_status'   => 'publish',
            'post_author'   => 1 //TODO: What's this value?
        );

        $post_id = wp_insert_post($new_post);
        $new_post->ID = $post_id;
        return $new_post;
    }

    public function update_post_data(array $existing_post, array $data)
    {
        $post = array(
            'ID' => $existing_post->ID,
            'post_title' => wp_strip_all_tags($data['PostTitle']),
            'post_content' => array_key_exists('PostContentHTML', $data) ? $data['PostContentHTML'] : '',
            'post_date' => $data['PublishedDate']
        );

        wp_update_post($post);

        // TODO: Fix this
        // foreach ($this->fields as $field) {
        //     add_metadata('post', $post_id, "PASSLESYNC_{$field}", $data[$field]);
        // }
    }

    public function delete_post(int $post_id)
    {        
        return wp_delete_post($post_id, true);
    }

    public function get_passle_authors()
    {
        $authors = get_posts(array(
            'numberposts'   => -1,
            'post_type'     => array(PASSLESYNC_AUTHOR_TYPE),
        ));

        if (empty($authors)) {
            return array();
        }

        array_walk($authors, function($a) {
            $a = $this->apply_meta_data_to_author($a);
        });

        return $authors;
    }

    public function get_passle_author_by_shortcode(string $author_shortcode)
    {
        $authors = $this->get_passle_authors();

        $matching_authors = array_filter($authors, function ($author) use ($data) {
            return $author->author_shortcode === $author_shortcode;
        });

        if (count($matching_authors) > 0) {
            $author = reset($matching_authors);
            return $author;
        } else {
            return null;
        }
    }

    public function update_passle_author(array $data)
    {
        // If the author isn't for this Passle, ignore it
        $passle = get_option(PASSLESYNC_SHORTCODE);
        if (!in_array($data['PassleShortcode'], $passle))
        {
            return;
        }

        // Find if there's an existing author with this shortcode
        // Update it, if so
        $id = 0;
        $existing_author = $this->get_passle_author_by_shortcode($data['Shortcode']);
        if ($existing_author != null) {
            $id = $existing_author->ID;
        }

        // TODO: Consider making this only update fields that are set in $data, and retaining existing values otherwise
        $author_name = isset($data['Name']) ? $data['Name'] : '';
        // $post_date = isset($data['PublishedDate']) ? $data['PublishedDate'] : '';
        // $post_content = isset($data['PostContentHtml']) ? $data['PostContentHtml'] : '';
        $author_shortcode = isset($data['Shortcode']) ? $data['Shortcode'] : '';
        $passle_shortcode = isset($data['PassleShortcode']) ? $data['PassleShortcode'] : '';
        // $post_authors = isset($data['Authors']) ? implode(", ", Utils::array_select($data['Authors'], "Name")) : '';
        // $post_is_repost = isset($data['IsRepost']) ? $data['IsRepost'] : false;
        // $post_read_time = isset($data['EstimatedReadTimeInSeconds']) ? $data['EstimatedReadTimeInSeconds'] : 0;
        // $post_tags = isset($data['Tags']) ? implode(", ", $data['Tags']) : '';
        // $post_image = isset($data['ImageUrl']) ? $data['ImageUrl'] : '';
        // $post_preview = isset($data['ContentTextSnippet']) ? $data['ContentTextSnippet'] : '';

        $new_author = array(
            'ID'                => $id,
            'post_title'        => $post_title,
            // 'post_date'         => $post_date,
            'post_type'         => PASSLESYNC_AUTHOR_TYPE,
            // 'post_content'      => $post_content,
            'post_status'       => 'publish',
            'comment_status'    => 'closed',
            'meta_input'    => array(
                'author_shortcode'    => $author_shortcode,
                'passle_shortcode'  => $passle_shortcode,
                // 'post_authors'      => $post_authors,
                // 'post_is_repost'    => $post_is_repost,
                // 'post_read_time'    => $post_read_time,
                // 'post_tags'         => $post_tags,
                // 'post_image'        => $post_image,
                // 'post_preview'      => $post_preview,
            )
        );

        $author_id = wp_insert_post($new_author, true);
        if ($author_id != $id)
        {
            $new_author->ID = $author_id;
        }

        return $new_author;
    }

    public function create_new_blank_author()
    {
        $new_author = array(
            'post_type'     => PASSLESYNC_AUTHOR_TYPE,
            'post_status'   => 'publish',
            'post_author'   => 1 //TODO: What's this value?
        );

        $author_id = wp_insert_post($new_author);
        $new_author->ID = $author_id;
        return $new_author;
    }

    public function update_author_data(array $existing_author, array $data)
    {
        $author = array(
            'ID' => $existing_author->ID,
            'post_title' => wp_strip_all_tags($data['Name']),
            // 'post_content' => array_key_exists('PostContentHTML', $data) ? $data['PostContentHTML'] : '',
            // 'post_date' => $data['PublishedDate']
        );

        wp_update_post($author);

        // TODO: Fix this
        // foreach ($this->fields as $field) {
        //     add_metadata('post', $post_id, "PASSLESYNC_{$field}", $data[$field]);
        // }
    }

    public function delete_author(int $author_id)
    {        
        return wp_delete_post($author_id, true);
    }

    public function get_or_update_items(string $storage_key, $callback)
    {
        $items = get_option($storage_key);
        
        if (gettype($items) != "array" || count($items) == 0 || reset($items) == null) {
            $items = $callback();
        }

        return $items;
    }
}
