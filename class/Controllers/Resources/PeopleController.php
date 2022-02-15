<?php

namespace Passle\PassleSync\Controllers\Resources;

use Exception;
use Passle\PassleSync\Controllers\Resources\ResourceControllerBase;
use Passle\PassleSync\Models\PaginatedResponse;
use Passle\PassleSync\Models\Person;
use Passle\PassleSync\SyncHandlers\Handlers\AuthorHandler;
use Passle\PassleSync\Services\Content\PeopleWordpressContentService;
use Passle\PassleSync\Services\PassleContentService;
use Passle\PassleSync\SyncHandlers\SyncHandlerBase;

class PeopleController extends ResourceControllerBase
{
  protected PeopleWordpressContentService $wordpress_content_service;
  protected PassleContentService $passle_content_service;

  private SyncHandlerBase $sync_handler;

  public function __construct(
    PeopleWordpressContentService $wordpress_content_service,
    PassleContentService $passle_content_service,
    AuthorHandler $sync_handler
  ) {
    parent::__construct("people");
    $this->sync_handler = $sync_handler;
    $this->passle_content_service = $passle_content_service;
    $this->wordpress_content_service = $wordpress_content_service;
  }

  public function refresh_all()
  {
    $this->passle_content_service->update_all_passle_authors_from_api();
  }

  public function get_all($request)
  {
    $wp_people = $this->wordpress_content_service->get_items();
    $api_people = $this->passle_content_service->get_stored_passle_authors_from_api();

    // Create Person objects from models
    $wp_person_models = array_map(fn ($person) => Person::fromWordpressPerson($person)->to_array(), $wp_people);
    $api_person_models = array_map(fn ($person) => Person::fromPasslePerson($person)->to_array(), $api_people);

    // Merge arrays
    $all_models = array_merge($wp_person_models, $api_person_models);
    $unique_shortcodes = array_unique(array_column($all_models, "shortcode"));
    $unique_models = array_intersect_key($all_models, $unique_shortcodes);

    // Sort unique models by name a-z
    usort($unique_models, fn ($a, $b) => $a["name"] <=> $b["name"]);

    // Paginate response
    $current_page = $request["currentPage"] ?? 1;
    $items_per_page = $request["itemsPerPage"] ?? 20;

    return PaginatedResponse::make($unique_models, $current_page, $items_per_page);
  }

  public function sync_all($request)
  {
    $this->sync_handler->sync_all();
  }

  public function delete_all($request)
  {
    $this->sync_handler->delete_all();
  }

  public function sync_many($request)
  {
    $data = $request->get_json_params();

    $shortcodes = $data["shortcodes"] ?? null;

    if ($shortcodes == null) {
      throw new Exception("Missing shortcodes parameter", 400);
    }

    $people = $this->passle_content_service->get_people($shortcodes);

    $this->sync_handler->sync_many($people);
  }

  public function delete_many($request)
  {
    $data = $request->get_json_params();

    $shortcodes = $data["shortcodes"] ?? null;

    if ($shortcodes == null) {
      throw new Exception("Missing shortcodes parameter", 400);
    }

    $people = $this->passle_content_service->get_people($shortcodes);

    $this->sync_handler->delete_many($people);
  }
}
