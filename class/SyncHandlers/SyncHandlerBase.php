<?php

namespace Passle\PassleSync\SyncHandlers;

use Passle\PassleSync\Services\ApiService;

abstract class SyncHandlerBase
{
    protected $api_service;

    protected $existing_content;
    protected $passle_content;

    protected abstract function get_existing_content();
    protected abstract function get_passle_content(string $passle_shortcode);

    protected abstract function sync_all_impl();
    protected abstract function sync_one_impl(array $data);
    protected abstract function delete_one_impl(array $data);
    protected abstract function sync(int $entity_id, array $data);
    protected abstract function delete(int $entity_id);

    private $passle_shortcode;

    public function __construct(ApiService $api_service)
    {
        $this->api_service = $api_service;
        $this->passle_shortcode = get_option(PASSLESYNC_SHORTCODE);
    }

    public function sync_all()
    {
        try {
            $this->existing_content = $this->get_existing_content();
            $this->passle_content = $this->get_passle_content($this->passle_shortcode);
            $this->sync_all_impl();
        } catch (\Exception $ex) {
            error_log("Failed to sync all items: {$ex->getMessage()}");
        }
    }

    public function sync_one(array $data)
    {
        try {
            $this->existing_content = $this->get_existing_content();
            $this->sync_one_impl($data);
        } catch (\Exception $ex) {
            error_log("Failed to sync item: {$ex->getMessage()}");
        }
    }

    public function delete_one(array $data)
    {
        try {
            $this->existing_content = $this->get_existing_content();
            $this->delete_one_impl($data);
        } catch (\Exception $ex) {
            error_log("Failed to delete item: {$ex->getMessage()}");
        }
    }
}
