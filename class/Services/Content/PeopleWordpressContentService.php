<?php

namespace Passle\PassleSync\Services\Content;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;

class PeopleWordpressContentService extends WordpressContentServiceBase implements IWordpressContentService
{
    private $item_type = PASSLESYNC_AUTHOR_TYPE;

    public function get_items()
    {
        return $this->get_items_by_type($this->item_type);
    }

    public function update_item(array $data)
    {
        // If the author isn't for this Passle, ignore it
        $passle_shortcodes = get_option(PASSLESYNC_SHORTCODE);
        if (!in_array($data['PassleShortcode'], $passle_shortcodes))
        {
            return "Passle shortcode (" . $data['PassleShortcode'] . ") is not in list (" . join(', ', $passle_shortcodes) . ")";
        }

        // Find if there's an existing author with this shortcode
        // Update it, if so
        $id = 0;
        $existing_author = $this->get_item_by_shortcode($data['Shortcode'], 'author_shortcode');
        if ($existing_author != null) {
            $id = $existing_author->ID;
        }

        // TODO: Consider making this only update fields that are set in $data, and retaining existing values otherwise
        $author_name = isset($data['Name']) ? $data['Name'] : '';
        $author_shortcode = isset($data['Shortcode']) ? $data['Shortcode'] : '';
        $passle_shortcode = isset($data['PassleShortcode']) ? $data['PassleShortcode'] : '';
        
        $new_item = array(
            'ID'                => $id,
            'post_title'        => $author_name,
            'post_type'         => $this->item_type,
            'post_status'       => 'publish',
            'comment_status'    => 'closed',
            'meta_input'    => array(
                'author_shortcode'   => $author_shortcode,
                'passle_shortcode'   => $passle_shortcode,
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
        // $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_shortcode', "");
        return $item;
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
}
