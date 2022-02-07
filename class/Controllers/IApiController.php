<?php

namespace Passle\PassleSync\Controllers;

interface IApiController
{
    public function register_api_routes();
    public function get_all_items($data);
    public function get_stored_items_from_api();
    public function update_items();
    public function update_item($data);
    public function delete_existing_items();
    public function delete_existing_item(object $data);
}
