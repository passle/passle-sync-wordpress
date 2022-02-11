<?php

namespace Passle\PassleSync\Services\Content;

class PostsWordpressContentService extends WordpressContentServiceBase implements IWordpressContentService
{
  private $item_type = PASSLESYNC_POST_TYPE;

  public function get_items()
  {
    return $this->get_items_by_type($this->item_type);
  }

  public function apply_meta_data_to_item(object $item)
  {
    $meta = get_post_meta($item->ID);
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_shortcode', "");
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'passle_shortcode', "default");
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_authors', "");
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_is_repost', "");
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_read_time', 0);
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_tags', "");
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_image', "");
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_image_html', "");
    $item = $this->apply_individual_meta_data_to_item($item, $meta, 'post_preview', "");
    return $item;
  }
}
