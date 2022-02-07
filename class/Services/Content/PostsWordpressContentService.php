<?php

namespace Passle\PassleSync\Services\Content;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

class PostsWordpressContentService extends WordpressContentServiceBase implements IWordpressContentService
{
    private $item_type = PASSLESYNC_POST_TYPE;

    public function get_items()
    {
        return $this->get_items_by_type($this->item_type);
    }

    public function update_item(array $data)
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
        $existing_post = $this->get_item_by_shortcode($data['PostShortcode'], 'post_shortcode');
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
        $post_image_html = isset($data['FeaturedItemHTML']) ? $data['FeaturedItemHTML'] : '';
        $post_preview = isset($data['ContentTextSnippet']) ? $data['ContentTextSnippet'] : '';

        $new_item = array(
            'ID'                => $id,
            'post_title'        => $post_title,
            'post_date'         => $post_date,
            'post_type'         => $this->item_type,
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
                'post_image_html'   => $post_image_html,
                'post_preview'      => $post_preview,
            )
        );

        $new_id = wp_insert_post($new_item, true);
        if ($new_id != $id)
        {
            $new_item->ID = $new_id;
        }

        return $new_item;
    }

    public function apply_meta_data_to_item(object $item)
    {    
        $meta = get_post_meta($item->ID);
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_shortcode', "");
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'passle_shortcode', "default");
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_authors', "");
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_is_repost', "");
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_read_time', 0);
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_tags', "");
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_image', "");
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_image_html', "");
        $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_preview', "");
        return $item;
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
}
