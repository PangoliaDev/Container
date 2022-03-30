<?php

namespace Pangolia\ContainerTests;

$GLOBALS['container'] = [];

define( 'PANGOLIA_PHPUNIT', true );
define( 'PANGOLIA_DIR', __DIR__ );
define( 'PANGOLIA_FILE', __FILE__ );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG', true );

$composer = require __DIR__ . '/../vendor/autoload.php';
define( 'COMPOSER_PREFIXES', $composer->getPrefixesPsr4() );

require_once __DIR__ . '/unit/ContainerTestCase.php';