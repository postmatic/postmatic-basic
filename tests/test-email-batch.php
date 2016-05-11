<?php

class EmailBatchTest extends WP_UnitTestCase {

	function test_local_default_from_address() {
		$this->assertEquals(
			'postmatic@example.org',
			Prompt_Email_Batch::default_from_email(),
			'Expected default from address to reflect test domain.'
		);
	}

	function tests_api_default_from_address() {
		Prompt_Core::$options->set( 'email_transport', Prompt_Enum_Email_Transports::API );

		$this->assertEquals(
			'hello@email.gopostmatic.com',
			Prompt_Email_Batch::default_from_email(),
			'Expected default from address to be the Postmatic mail server default.'
		);
	}
}
