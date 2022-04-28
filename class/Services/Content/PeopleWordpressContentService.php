<?php

namespace Passle\PassleSync\Services\Content;

class PeopleWordpressContentService extends WordpressContentServiceBase implements IWordpressContentService
{
  public function __construct()
  {
    parent::__construct(PASSLESYNC_AUTHOR_TYPE);
  }
}
