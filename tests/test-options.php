<?php

class OptionsTest extends WP_UnitTestCase {

	function test_internal_key() {
		$options = new Prompt_Options();
		$this->assertEquals(
			10,
			has_action( 'plugins_loaded', array( $options, 'generate_internal_key' ) ),
			'Expected key generation action to be added.'
		);
	}

	function test_author_subscription_with_moderation() {
		$options = new Prompt_Options();
		$this->assertFalse( $options->get( 'auto_subscribe_authors' ), 'Expected automatic author subscription to default to false.' );

		$options->set( 'enabled_message_types', array( Prompt_Enum_Message_Types::COMMENT_MODERATION ) );

		$options = new Prompt_Options();
		$this->assertTrue( $options->get( 'auto_subscribe_authors' ), 'Expected automatic author subscription to be true with moderation.' );
	}
}
