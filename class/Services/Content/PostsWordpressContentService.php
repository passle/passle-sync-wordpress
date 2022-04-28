<?php

namespace Passle\PassleSync\Services\Content;

class PostsWordpressContentService extends WordpressContentServiceBase implements IWordpressContentService
{
  public function __construct()
  {
    parent::__construct(PASSLESYNC_POST_TYPE);
  }
}
