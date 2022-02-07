<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Services\PassleContentService;

abstract class SyncHandlerBase
{
    protected $passle_content_service;
    private $passle_shortcode;

    public function __construct(PassleContentService $passle_content_service)
    {
        $this->passle_content_service = $passle_content_service;
        $this->passle_shortcode = get_option(PASSLESYNC_SHORTCODE);
    }

    protected abstract function create_blank_item();
    protected abstract function sync_all_impl();
    protected abstract function sync_one_impl(array $data);
    protected abstract function delete_all_impl();
    protected abstract function delete_one_impl(array $data);
    protected abstract function sync(object $entity, array $data);
    protected abstract function delete(object $entity);

    public function sync_all()
    {
        try {
            $this->sync_all_impl();
        } catch (\Exception $ex) {
            error_log("Failed to sync all items: {$ex->getMessage()}");
        }
    }

    public function sync_one(array $data)
    {
        try {
            $this->sync_one_impl($data);
        } catch (\Exception $ex) {
            error_log("Failed to sync item: {$ex->getMessage()}");
        }
    }

    public function delete_all()
    {
        try {
            $this->delete_all_impl();
        } catch (\Exception $ex) {
            error_log("Failed to delete all items: {$ex->getMessage()}");
        }
    }

    public function delete_one(array $data)
    {
        try {
            $this->delete_one_impl($data);
        } catch (\Exception $ex) {
            error_log("Failed to delete item: {$ex->getMessage()}");
        }
    }

    public function compare_items(array $passle_items, array $existing_items, string $passle_item_shortcode_property, string $existing_item_shortcode_property)
    {
        $passle_shortcodes = Utils::array_select($passle_items, $passle_item_shortcode_property);

        $existing_shortcodes = [];
        // Create a dict for easier access later
        $existing_items_by_shortcode = [];

        foreach ($existing_items as $item) {
            array_push($existing_shortcodes, $item->{$existing_item_shortcode_property});
            $existing_items_by_shortcode[$item->{$existing_item_shortcode_property}] = $item;
        }

        $all_shortcodes = array_unique(array_merge($passle_shortcodes, $existing_shortcodes));

        $shortcodes_to_add = array_filter($passle_shortcodes, fn ($shortcode) => !in_array($shortcode, $existing_shortcodes));
        $shortcodes_to_remove = array_filter($existing_shortcodes, fn ($shortcode) => !in_array($shortcode, $passle_shortcodes));
        $shortcodes_to_update = array_filter($all_shortcodes, fn ($shortcode) => !in_array($shortcode, $shortcodes_to_add) && !in_array($shortcode, $shortcodes_to_remove));

        // Add
        $items_to_add = array_filter($passle_items, fn ($item) => in_array($item[$passle_item_shortcode_property], $shortcodes_to_add));
        foreach ($items_to_add as $item) {
            $new_item = $this->create_blank_item();
            $this->sync($new_item, $item);
        }

        // Update
        $items_to_update = array_filter($passle_items, fn ($item) => in_array($item[$passle_item_shortcode_property], $shortcodes_to_update));
        foreach ($items_to_update as $item) {
            $existing_item = $existing_items_by_shortcode[$item[$passle_item_shortcode_property]];
            $this->sync($existing_item, $item);
        }

        // Remove
        $items_to_remove = array_filter($existing_items, fn ($item) => in_array($item->{$existing_item_shortcode_property}, $shortcodes_to_remove));
        foreach ($items_to_remove as $item) {
            $this->delete($item);
        }

        return;
    }
}
