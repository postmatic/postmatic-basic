<?php

class CoreTest extends WP_UnitTestCase {

	function testOptions() {
		$this->assertNotEmpty( Prompt_Core::$options );
	}

	function testVersion() {
		$short_version = Prompt_Core::version();
		$long_version = Prompt_Core::version( $full = true );

		$this->assertNotEmpty( $short_version, 'Expected a non-empty version.' );
		$this->assertGreaterThanOrEqual( $short_version, $long_version, 'Expected a longer full version.' );
		$this->assertStringStartsWith( $short_version, $long_version, 'Expected compatible versions.' );
	}

	function testDefaultOptionsFilter() {
		add_filter( 'prompt/default_options', create_function( '$o', 'return array_merge( $o, array( "foo" => "bar" ) );' ) );

		Prompt_Core::action_plugins_loaded();

		$this->assertEquals( 'bar', Prompt_Core::$options->get( 'foo' ), 'Expected to find added option.' );
	}

	function testVersionChange() {
		Prompt_Core::$options->set( 'last_version', 'foo' );
		Prompt_Core::$options->set( 'upgrade_required', true );
		Prompt_Core::$options->set( 'skip_download_intro', true );

		Prompt_Core::detect_version_change();

		$this->assertFalse(
			\Prompt_Core::$options->get( 'upgrade_required' ),
			'Expected the upgrade required flag to be false.'
		);
		$this->assertFalse(
			\Prompt_Core::$options->get( 'skip_download_intro' ),
			'Expected the skip download intro flag to be false.'
		);
		$this->assertNotEmpty(
			Prompt_Core::$options->get( 'whats_new_notices' ),
			'Expected a what\'s new notice.'
		);
	}

	function testLabsNotice() {
		$this->assertInstanceOf( 'Prompt_Admin_Labs_Notice', Prompt_Core::labs_notice() );
	}
}