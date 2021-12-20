<?php

namespace Passle\PassleSync\Services;

class ApiService
{
    private $sync_service;

    public function __construct(SyncService $sync_service)
    {
        $this->sync_service = $sync_service;
    }

    public function register_api_routes()
    {
        register_rest_route('passlesync/v1', '/sync-all', array(
            'methods' => 'POST',
            'callback' => array($this->sync_service, "sync_all"),
        ));
    }
}
