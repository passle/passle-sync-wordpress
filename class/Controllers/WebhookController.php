<?php

namespace Passle\PassleSync\Controllers;

use Passle\PassleSync\Actions\Resources\PeopleWebhookActions;
use Passle\PassleSync\Actions\Resources\PostsWebhookActions;
use Passle\PassleSync\Actions\UpdateFeaturedPostAction;
use Passle\PassleSync\Models\WebhookAction;
use Passle\PassleSync\ResponseFactories\PingResponseFactory;
use WP_REST_Request;

class WebhookController extends ControllerBase
{
  public static function init()
  {
    static::register_route("webhook", "POST", "handle", "validate_passle_webhook_request");
  }

  public static function handle(WP_REST_Request $request)
  {
    $action = static::get_required_parameter($request, "Action");
    if (is_a($action, 'WP_Error')) {
      return $action;
    }

    $data = static::get_required_parameter($request, "Data");
    if (is_a($data, 'WP_Error')) {
      return $data;
    }

    switch ($action) {
      case WebhookAction::SYNC_POST:
        return PostsWebhookActions::update($data["Shortcode"]);
      case WebhookAction::DELETE_POST:
        return PostsWebhookActions::delete($data["Shortcode"]);
      case WebhookAction::SYNC_AUTHOR:
        return PeopleWebhookActions::update($data["Shortcode"]);
      case WebhookAction::DELETE_AUTHOR:
        return PeopleWebhookActions::delete($data["Shortcode"]);
      case WebhookAction::UPDATE_FEATURED_POST:
        return UpdateFeaturedPostAction::execute($data["Shortcode"], $data["IsFeaturedOnPasslePage"], $data["IsFeaturedOnPostPage"]);
      case WebhookAction::PING:
        return PingResponseFactory::make($request);
    }
  }
}
