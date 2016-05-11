<?php

class ConfiguratorTest extends WP_UnitTestCase {

	function tearDown() {
		parent::tearDown();
		Prompt_Core::$options->reset();
	}

	function testPullConfiguration() {

		$messages = new stdClass();
		$messages->{'test-message'} = 'This is a test message.';

		$response_body = array(
			'site' => array( 'url' => admin_url( 'admin-ajax.php' ) ),
			'messages' => $messages,
			'plan_data' => array(),
			'configuration' => array( 'email_transport' => 'test' ),
		);

		$response = array(
			'response' => array( 'code' => 200 ),
			'body' => json_encode( $response_body ),
		);

		$mock_client = $this->getMock( 'Prompt_Api_Client' );
		$mock_client->expects( $this->once() )
			->method( 'get_site' )
			->will( $this->returnValue( $response ) );

		$configurator = new Prompt_Configurator( $mock_client );

		$status = $configurator->pull_configuration();

		$this->assertTrue( $status, 'Expected true status return value.' );

		$this->assertEquals( $messages, Prompt_Core::$options->get( 'messages' ), 'Expected messages option to be set.' );
		$this->assertEquals(
			$response_body['configuration']['email_transport'],
			Prompt_Core::$options->get( 'email_transport' ),
			'Expected email transport option to be set.'
		);

	}
}