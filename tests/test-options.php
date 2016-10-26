<?php

class OptionsTest extends WP_UnitTestCase {

	function test_internal_key() {
		$options = new Prompt_Options();
		$this->assertNotEmpty( $options->get( 'internal_key' ) );
	}
}
