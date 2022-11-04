<?php

namespace Passle\PassleSync\Models;

class WebhookAction
{
  const SYNC_POST = 1;
  const DELETE_POST = 2;
  const SYNC_AUTHOR = 3;
  const DELETE_AUTHOR = 4;
  const UPDATE_FEATURED_POST = 5;
  const PING = 6;
}
