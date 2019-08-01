<?php

define('WP_SIDEWHEELS_CONFIG', __DIR__ . '/config.php' );

// First we need to load the composer autoloader so we can use WP Mock
require_once __DIR__ . '/../vendor/autoload.php';

// Now call the bootstrap method of WP Mock
WP_Mock::bootstrap();