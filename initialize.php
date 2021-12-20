<?php

namespace Passle\PassleSync;

use DI\Container;

// Initialize DI
$container = new Container();
$passle_sync = $container->get(PassleSync::class);

$passle_sync->initialize();
