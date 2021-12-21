<?php

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Utils\UrlFactory;

class ContentService
{
    public function get_passle_posts()
    {
        $posts = get_posts(array(
            'numberposts'   => -1,
            'post_type'     => array('PasslePost'),
        ));

        if (empty($posts)) {
            return array();
        }

        return $posts;
    }

    public function get_passle_post($data)
    {
        $posts = get_posts(array(
            'ID'            => $data['id'],
            'numberposts'   => 1,
            'post_type'     => array('PasslePost'),
        ));

        if (empty($posts)) {
            $error = new WP_Error( 'no_id', 'Invalid id', array( 'status' => 404 ) );
            $error->add_data($data);
            return $error;
        }

        return $posts[0];
    }

    public function update_passle_post($data)
    {
        // Find if there's an existing post with this shortcode
        // Update it, if so
        $id = 0;
        $posts = get_passle_posts();

        $matching_posts = array_filter($posts, function ($p) use ($data) {
            return $p->post_shortcode === $data['PostShortcode'];
        });

        // If there's a matching post, get its ID to ensure we update it
        if (count($matching_posts) > 0) {
            $id = $matching_posts[0]->ID;
        }

        $new_post = array(
            'ID'                => $id,
            'post_title'        => $post_data['PostTitle'],
            'post_date'         => $post_data['PublishedDate'],
            'post_type'         => 'PasslePost',
            'post_content'      => $post_data['ContentTextSnippet'],
            'post_status'       => 'publish',
            'comment_status'    => 'closed',
            'meta_input'    => array(
                'post_shortcode'    => $post_data['PostShortcode'],
                'passle_shortcode'  => $post_data['PassleShortcode']
            )
        );

        return $new_post;
    }
}
