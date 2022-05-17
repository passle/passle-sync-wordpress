<?php

namespace Passle\PassleSync\Controllers;

use \WP_REST_Request;

interface IApiController
{
  public function get_all(WP_REST_Request $request);
  public function sync_all(WP_REST_Request $request);
  public function delete_all(WP_REST_Request $request);

  public function sync_many(WP_REST_Request $request);
  public function delete_many(WP_REST_Request $request);
}
