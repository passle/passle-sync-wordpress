<?php

namespace Passle\PassleSync\Controllers\Resources;

use Passle\PassleSync\Controllers\Resources\ResourceControllerBase;
use Passle\PassleSync\Models\Resources\PersonResource;

class PeopleController extends ResourceControllerBase
{
  const RESOURCE = PersonResource::class;
}
