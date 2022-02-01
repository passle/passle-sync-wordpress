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
            $id = $matching_posts[0]->ID;
        }

        $new_post = array(
            'ID'                => $id,
            'post_title'        => $data['PostTitle'],
            'post_date'         => $data['PublishedDate'],
            'post_type'         => PASSLESYNC_POST_TYPE,
            'post_content'      => $data['PostContentHtml'],
            'post_status'       => 'publish',
            'comment_status'    => 'closed',
            'meta_input'    => array(
                'post_shortcode'    => $data['PostShortcode'],
                'passle_shortcode'  => $data['PassleShortcode'],
                'post_authors'      => implode(", ", Utils::array_select($data['Authors'], "Name")),
                'post_is_repost'    => $data['IsRepost'],
                'post_read_time'    => $data['EstimatedReadTimeInSeconds'],
                'post_tags'         => implode(", ", $data['Tags']),
                'post_image'        => $data["ImageUrl"],
                'post_preview'      => $data['ContentTextSnippet'],
            )
        );

        $pid = wp_insert_post($new_post, true);

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
