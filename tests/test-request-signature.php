<?php

class RequestSignaureTest extends WP_Ajax_UnitTestCase {
	/** @var int */
	protected $_timestamp = null;
	/** @var string */
	protected $_token = null;
	/** @var string */
	protected $_signature = null;
	/** @var string */
	protected $_prompt_key = null;

	function setUp() {
		parent::setUp();
		$this->_timestamp = time();
		$this->_token = md5( date( 'c' ) );
		$this->_prompt_key = '9ca1bc2515c5ddb67a7dbe038e994c9c';
		$this->_signature = hash_hmac( 'sha256', $this->_timestamp . $this->_token, $this->_prompt_key );
	}

	function test_valid_signature() {
		$signature = new Prompt_Request_Signature( $this->_prompt_key, $this->_timestamp, $this->_token );

		$this->assertTrue(
			$signature->validate( $this->_signature ),
			'Expected test signature to be valid.'
		);
	}

	function test_invalid_signature() {
		$signature = new Prompt_Request_Signature( $this->_prompt_key, $this->_timestamp, $this->_token );

		$valid = $signature->validate( 'foo' );

		$this->assertInstanceOf(
			'WP_Error',
			$valid,
			'Expected an error validating a bad signature.'
		);

		$this->assertEquals(
			Prompt_Enum_Error_Codes::SIGNATURE,
			$valid->get_error_code(),
			'Expected a signature error code'
		);

		$this->assertContains(
			'signature',
			$valid->get_error_message(),
			'Expected signature in the error message'
		);

		$this->assertArrayHasKey(
			'signature',
			$valid->get_error_data(),
			'Expected signature in the error data.'
		);

		$this->assertArrayHasKey(
			'timestamp',
			$valid->get_error_data(),
			'Expected timestamp in the error data.'
		);

		$this->assertArrayHasKey(
			'token',
			$valid->get_error_data(),
			'Expected invalid timestamp in the error data.'
		);
	}

	function test_invalid_timestamp() {
		$signature = new Prompt_Request_Signature( $this->_prompt_key, time() - DAY_IN_SECONDS, $this->_token );

		$valid = $signature->validate( $this->_signature );

		$this->assertInstanceOf(
			'WP_Error',
			$valid,
			'Expected an error validating a signature with a bad timestamp.'
		);
	}

}

