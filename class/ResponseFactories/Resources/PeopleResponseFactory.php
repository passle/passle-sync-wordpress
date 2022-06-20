<?php

namespace Passle\PassleSync\ResponseFactories\Resources;

use Passle\PassleSync\Models\Resources\PersonResource;
use Passle\PassleSync\ResponseFactories\Resources\ResourceResponseFactoryBase;

class PeopleResponseFactory extends ResourceResponseFactoryBase
{
  const RESOURCE = PersonResource::class;
}
