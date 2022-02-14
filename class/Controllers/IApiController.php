<?php

namespace Passle\PassleSync\Controllers;

interface IApiController
{
  public function get_all($request);
  public function sync_all($request);
  public function delete_all($request);

  public function sync_many($request);
  public function delete_many($request);
}
