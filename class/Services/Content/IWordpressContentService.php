<?php

namespace Passle\PassleSync\Services\Content;

interface IWordpressContentService
{
    public function get_items();
    public function apply_meta_data_to_item(object $item);
}
