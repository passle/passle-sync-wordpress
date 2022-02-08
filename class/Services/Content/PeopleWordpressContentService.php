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

    public function apply_meta_data_to_item(object $item)
    {    
        $meta = get_post_meta($item->ID);
        // $item = $this->apply_individual_meta_data_to_post($item, $meta, 'post_shortcode', "");
        return $item;
    }
}
