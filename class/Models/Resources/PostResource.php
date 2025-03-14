<?php

namespace Passle\PassleSync\Models\Resources;

use Passle\PassleSync\Controllers\Resources\PostsController;
use Passle\PassleSync\Models\Admin\Post;
use Passle\PassleSync\Models\Resources\ResourceBase;
use Passle\PassleSync\PostTypes\PasslePostCpt;
use Passle\PassleSync\ResponseFactories\Resources\PostsResponseFactory;
use Passle\PassleSync\Services\Content\Passle\PasslePostsContentService;
use Passle\PassleSync\Services\Content\Wordpress\WordpressPostsContentService;
use Passle\PassleSync\SyncHandlers\PostHandler;

class PostResource extends ResourceBase
{
  const NAME_SINGULAR = "post";
  const NAME_PLURAL = "posts";
  const DISPLAY_NAME_SINGULAR = "post";
  const DISPLAY_NAME_PLURAL = "posts";
  const CONTROLLER_NAME = PostsController::class;
  const RESPONSE_FACTORY_NAME = PostsResponseFactory::class;
  const PASSLE_CONTENT_SERVICE_NAME = PasslePostsContentService::class;
  const WORDPRESS_CONTENT_SERVICE_NAME = WordpressPostsContentService::class;
  const SYNC_HANDLER_NAME = PostHandler::class;
  const ADMIN_MODEL_NAME = Post::class;
  const CPT_NAME = PasslePostCpt::class;
  const LAST_SYNCED_PAGE_OPTION_NAME = "last_synced_posts_page";
}
