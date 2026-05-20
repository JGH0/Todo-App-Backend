<?php

/**
 * Test Bootstrap
 *
 * Sets up the testing environment before the framework boots.
 * Disables the debug toolbar that would wrap JSON API responses in HTML.
 * The framework's own Test/bootstrap.php handles ENVIRONMENT constant.
 */

// Disable debug toolbar before the framework defines CI_DEBUG
defined('CI_DEBUG') || define('CI_DEBUG', false);

// Ensure CI_ENVIRONMENT env var is set (framework checks this)
putenv('CI_ENVIRONMENT=testing');
$_SERVER['CI_ENVIRONMENT'] = 'testing';

// Load the framework's test bootstrap (defines ENVIRONMENT constant)
require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';
