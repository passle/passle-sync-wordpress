<?php

namespace Passle\PassleSync\Controllers;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\SyncHandlers\Handlers\AuthorHandler;
use Passle\PassleSync\Services\Content\PeopleWordpressContentService;
use Passle\PassleSync\Services\PassleContentService;

class PeopleApiController extends ApiControllerBase implements IApiController
{
    protected $fields = array(
        'Shortcode',
    );
    protected $passle_content_service;
    protected $wordpress_content_service;

    public function __construct(
        PeopleWordpressContentService $wordpress_content_service,
        PassleContentService $passle_content_service,
        AuthorHandler $sync_handler)
    {
        parent::__construct();
        $this->sync_handler = $sync_handler;
        $this->passle_content_service = $passle_content_service;
        $this->wordpress_content_service = $wordpress_content_service;
    }

    public function register_api_routes()
    {
        $this->register_route('/people', 'GET', 'get_all_items');
        $this->register_route('/people/api', 'GET', 'get_stored_items_from_api');
        $this->register_route('/people/api/update', 'GET', 'update_items');
        $this->register_route('/person/update', 'POST', 'update_item');
        $this->register_route('/people/delete', 'POST', 'delete_existing_items');
        $this->register_route('/person/delete', 'POST', 'delete_existing_item');
    }

    public function get_all_items($data)
    {
        return $this->wordpress_content_service->get_items();
    }

    public function get_stored_items_from_api()
    {
        return $this->passle_content_service->get_stored_passle_authors_from_api();
    }

    public function update_items()
    {
        return $this->passle_content_service->update_all_passle_authors_from_api();
    }

    public function update_item($data)
    {
        $author_data = $data->get_json_params();

        if (!isset($author_data)) {
            return new \WP_Error('no_data', 'You must include data to create a author', array('status' => 400));
        }

        if (!isset($author_data['Name'])) {
            return new \WP_Error('no_name', 'You must include a author name', array('status' => 400));
        }

        return $this->wordpress_content_service->update_item($author_data);
        // TODO: Use this
        // return $this->sync_handler->sync_one($data);
    }

    public function delete_existing_items()
    {
        return $this->sync_handler->delete_all();
    }

    public function delete_existing_item($data)
    {
        $author = $this->wordpress_content_service->get_item_by_shortcode($data['Shortcode']);
        return $this->sync_handler->delete($author);
    }
}