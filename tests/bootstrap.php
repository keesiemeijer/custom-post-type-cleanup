<?php
/**
 * PHPUnit bootstrap file
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once 'vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Load the cleanup class.
require dirname( dirname( __FILE__ ) ) . '/includes/functions.php';
require dirname( dirname( __FILE__ ) ) . '/includes/request.php';
require dirname( dirname( __FILE__ ) ) . '/includes/class-post-type-cleanup.php';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Load the cleanup testcase.
require dirname( dirname( __FILE__ ) ) . '/tests/testcase.php';
