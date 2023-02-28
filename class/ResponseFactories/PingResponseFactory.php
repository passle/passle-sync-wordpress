<?php

namespace Passle\PassleSync\ResponseFactories;

use Passle\PassleSync\Services\OptionsService;
use WP_REST_Request;

class PingResponseFactory
{
  public static function make(WP_REST_Request $request)
  {
    $options = OptionsService::get();

    return [
      "RemoteHostingType" => PASSLESYNC_REMOTE_HOSTING_TYPE,
      "PostPermalinkPrefix" => $options->post_permalink_prefix,
      "PersonPermalinkPrefix" => $options->person_permalink_prefix,
      "PreviewPermalinkPrefix" => $options->preview_permalink_prefix,
    ];
  }
}
