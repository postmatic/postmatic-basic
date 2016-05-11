<?php

$_tests_dir = getenv('WP_TESTS_DIR');
$_tests_dir = $_tests_dir ? $_tests_dir : '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	$plugin_dir = getenv( 'PLUGIN_DIR' );
	$plugin_dir = $plugin_dir ? $plugin_dir : dirname( dirname( __FILE__ ) );
	require $plugin_dir . '/postmatic.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

function _override_prompt_options( $options ) {
	$options['prompt_key'] = 'test';
	$options['site_icon'] = -1;
	return $options;
}
tests_add_filter( 'prompt/override_options', '_override_prompt_options' );

function _prompt_error_on_http( $continue, $request, $url ) {
	throw new Exception( 'Detected HTTP request during testing: ' . serialize( compact( 'url', 'request' ) ) );
}
tests_add_filter( 'pre_http_request', '_prompt_error_on_http', 10, 3 );

require $_tests_dir . '/includes/bootstrap.php';
require_once dirname( __FILE__ ) . '/unit-testcase.php';
require_once dirname( __FILE__ ) . '/mock-mailer-testcase.php';

// speed up password hashing
function wp_hash_password( $password ) {
	return $password;
}

// Disable update checking

class PucFactory {
	static function buildUpdateChecker( $url, $file, $slug ) {
		return null;
	}
	static function addVersion( $base, $method, $version ) {
		return null;
	}
}
