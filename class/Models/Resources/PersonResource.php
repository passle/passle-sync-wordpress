<?php

namespace Passle\PassleSync\Models\Resources;

use Passle\PassleSync\Controllers\Resources\PeopleController;
use Passle\PassleSync\Models\Admin\Person;
use Passle\PassleSync\Models\Resources\ResourceBase;
use Passle\PassleSync\PostTypes\PasslePersonCpt;
use Passle\PassleSync\ResponseFactories\Resources\PeopleResponseFactory;
use Passle\PassleSync\Services\Content\Passle\PasslePeopleContentService;
use Passle\PassleSync\Services\Content\Wordpress\WordpressPeopleContentService;
use Passle\PassleSync\SyncHandlers\AuthorHandler;

class PersonResource extends ResourceBase
{
  const NAME_SINGULAR = "person";
  const NAME_PLURAL = "people";
  const DISPLAY_NAME_SINGULAR = "author";
  const DISPLAY_NAME_PLURAL = "authors";
  const CONTROLLER_NAME = PeopleController::class;
  const RESPONSE_FACTORY_NAME = PeopleResponseFactory::class;
  const PASSLE_CONTENT_SERVICE_NAME = PasslePeopleContentService::class;
  const WORDPRESS_CONTENT_SERVICE_NAME = WordpressPeopleContentService::class;
  const SYNC_HANDLER_NAME = AuthorHandler::class;
  const ADMIN_MODEL_NAME = Person::class;
  const CPT_NAME = PasslePersonCpt::class;
  const LAST_SYNCED_PAGE_OPTION_NAME = "last_synced_people_page";

  public function get_shortcode_name()
  {
    return "Shortcode";
  }

  public function get_api_parameter_shortcode_name()
  {
    return ucfirst($this->name_singular) . "Shortcode";
  }
}
