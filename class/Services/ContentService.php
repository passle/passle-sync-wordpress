<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

class ContentService
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

    public function get_passle_post($data)
    {
        $posts = get_posts(array(
            'ID'            => $data['id'],
            'numberposts'   => 1,
            'post_type'     => array(PASSLESYNC_POST_TYPE),
        ));

        if (empty($posts)) {
            $error = new WP_Error( 'no_id', 'Invalid id', array( 'status' => 404 ) );
            $error->add_data($data);
            return $error;
        }

        $post = $posts[0];
        $post = $this->apply_meta_data_to_post($post);
        return $post;
    }

    public function update_passle_post($data)
    {
        // Find if there's an existing post with this shortcode
        // Update it, if so
        $id = 0;
        $posts = $this->get_passle_posts();

        $matching_posts = array_filter($posts, function ($p) use ($data) {
            return $p->post_shortcode === $data['PostShortcode'];
        });

        // If there's a matching post, get its ID to ensure we update it
        if (count($matching_posts) > 0) {
            $id = reset($matching_posts)->ID;
        }

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

    public function apply_meta_data_to_post($post)
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

    public function apply_individual_meta_data_to_post($post, $meta, $propName, $default)
    {
        if (!empty($meta[$propName])) {
            $post->{$propName} = $meta[$propName][0];
        } else {
            $post->{$propName} = $default;
        }
        return $post;
    }
}
