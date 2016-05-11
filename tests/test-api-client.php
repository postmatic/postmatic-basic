<?php

class ApiClientTest extends WP_UnitTestCase {

	private $mock;

	function setUp() {
		parent::setUp();
		$this->mock = create_function(
			'$url, $args = array()',
			'return array( "url" => $url, "args" => $args );'
		);
	}

	function testDefaultConstruction() {
		$endpoint = '/test';

		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->send( $endpoint );

		$expected_url = 'https://app.gopostmatic.com/api/v1' . $endpoint;
		$this->assertEquals( $expected_url, $response['url'] );

		$expected_auth = 'Basic ' . base64_encode( 'api:' . Prompt_Core::$options->get( 'prompt_key' ) );
		$this->assertEquals(
			$expected_auth,
			$response['args']['headers']['Authorization'],
			'Expected encoded key in authorization header.'
		);

		$expected_version = Prompt_Core::version( true );
		$this->assertEquals(
			$expected_version,
			$response['args']['headers']['X-Prompt-Core-Version'],
			'Expected long version in X-Prompt-Core-Version header.'
		);
	}

	function testKeyConstruction() {
		$key = 'testkey';

		$client = new Prompt_Api_Client( array(), $key, null, $this->mock );

		$response = $client->send( '' );

		$expected_auth = 'Basic ' . base64_encode( 'api:' . $key );
		$this->assertEquals(
			$expected_auth,
			$response['args']['headers']['Authorization'],
			'Expected encoded key in authorization header.'
		);
	}

	function testBaseUrlConstruction() {
		$base_url = 'test://test.url';
		$endpoint = '/endpoint';

		$client = new Prompt_Api_Client( array(), null, $base_url, $this->mock );

		$response = $client->send( $endpoint );

		$this->assertEquals( $base_url . $endpoint, $response['url'] );
	}

	function testRequestConstruction() {
		$header = array( 'X-Test' => 'test' );
		$timeout = 3;
		$defaults = array(
			'timeout' => $timeout,
			'headers' => array( $header ),
		);

		$client = new Prompt_Api_Client( $defaults, null, null, $this->mock );

		$response = $client->send( '' );

		$this->assertEquals( $timeout, $response['args']['timeout'], 'Expected supplied timeout to be used.' );
		$this->assertContains( $header, $response['args']['headers'] );
		$this->assertArrayHasKey( 'Authorization', $response['args']['headers'] );
	}

	function testRequestOverride() {
		$test_header = array( 'X-Test' => 'test' );
		$override_header = array( 'X-Test' => 'override' );
		$defaults = array(
			'headers' => array( $test_header ),
		);

		$client = new Prompt_Api_Client( $defaults, null, null, $this->mock );

		$response = $client->send( '', array( 'headers' => array( $override_header ) ) );

		$this->assertNotContains(
			$test_header,
			$response['args']['headers'],
			'Expected default header to be overridden.'
		);
		$this->assertContains(
			$override_header,
			$response['args']['headers'],
			'Expected default header to be overridden.'
		);
		$this->assertArrayHasKey( 'Authorization', $response['args']['headers'] );
	}

	function testAbsoluteUrl() {
		$url = 'http://override.url/endpoint';

		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->send( $url );

		$this->assertEquals( $url, $response['url'] );
	}

	function testGet() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->get( '' );

		$this->assertEquals( 'GET', $response['args']['method'] );
	}

	function testPost() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->post( '' );

		$this->assertEquals( 'POST', $response['args']['method'] );
	}

	function testHead() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->head( '' );

		$this->assertEquals( 'HEAD', $response['args']['method'] );
	}

	function testPut() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->put( '' );

		$this->assertEquals( 'PUT', $response['args']['method'] );
	}

	function testDelete() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->delete( '' );

		$this->assertEquals( 'DELETE', $response['args']['method'] );
	}

	function testUpgradeRequired() {
		$this->assertFalse( Prompt_Core::$options->get( 'upgrade_required'), 'Expected no upgrade required.' );

		$client = new Prompt_Api_Client( array(), null, null, array( $this, 'upgradeErrorMock' ) );

		$response = $client->send( '' );

		$this->assertEquals( 410, $response['response']['code'] );

		$this->assertTrue( Prompt_Core::$options->get( 'upgrade_required'), 'Expected upgrade required.' );

		Prompt_Core::$options->reset();
	}

	function upgradeErrorMock( $url, $args ) {
		return array( 'response' => array( 'code' => 410, 'message' => 'Upgrade!' ) );
	}

	function testError() {
		$client = new Prompt_Api_Client( array(), null, null, array( $this, 'connectionErrorMock' ) );

		$response = $client->send( '' );

		$this->assertInstanceOf( 'WP_error', $response );
	}

	function connectionErrorMock( $url, $args ) {
		return new WP_Error( 'http_request_failed', 'Failed to connect' );
	}

	function testGetSite() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->get_site();

		$this->assertEquals( 'GET', $response['args']['method'] );
		$this->assertContains( '/site', $response['url'], 'Expected a request to /site.' );
	}

	function testGetUndeliveredUpdates() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->get_undelivered_updates();

		$this->assertEquals( 'GET', $response['args']['method'] );
		$this->assertContains( '/updates/undelivered', $response['url'], 'Expected a request to /updates/undelivered.' );
	}

	function testPostOutboundMessagesArray() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$data = array( 'foo' => 'bar' );

		$response = $client->post_outbound_messages( $data );

		$this->assertEquals( 'POST', $response['args']['method'] );
		$this->assertEquals( 'application/json', $response['args']['headers']['Content-Type'] );
		$this->assertEquals( json_encode( $data ), $response['args']['body'] );
		$this->assertContains( '/outbound_messages', $response['url'], 'Expected a request to /outbound_messages.' );
	}

	function testPostOutboundMessagesString() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$data = '{"foo": "bar"}';

		$response = $client->post_outbound_messages( $data );

		$this->assertEquals( 'POST', $response['args']['method'] );
		$this->assertEquals( 'application/json', $response['args']['headers']['Content-Type'] );
		$this->assertEquals( $data, $response['args']['body'] );
		$this->assertContains( '/outbound_messages', $response['url'], 'Expected a request to /outbound_messages.' );
	}

	function testPostEvents() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$data = array( 'foo' => 'bar' );

		$response = $client->post_events( $data );

		$this->assertEquals( 'POST', $response['args']['method'] );
		$this->assertEquals( 'application/json', $response['args']['headers']['Content-Type'] );
		$this->assertEquals( json_encode( $data ), $response['args']['body'] );
		$this->assertContains( '/events', $response['url'], 'Expected a request to /events.' );
	}

	function testPostOutboundMessageBatch() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$data = array( 'foo' => 'bar' );

		$response = $client->post_outbound_message_batches( $data );

		$this->assertEquals( 'POST', $response['args']['method'] );
		$this->assertEquals( 'application/json', $response['args']['headers']['Content-Type'] );
		$this->assertEquals( json_encode( $data ), $response['args']['body'] );
		$this->assertContains( '/outbound_message_batches', $response['url'], 'Expected a request to /events.' );
	}

	function testPostInstantCallbacks() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$data = array( 'foo' => 'bar' );

		$response = $client->post_instant_callback( $data );

		$this->assertEquals( 'POST', $response['args']['method'] );
		$this->assertEquals( 'application/json', $response['args']['headers']['Content-Type'] );
		$this->assertEquals( json_encode( $data ), $response['args']['body'] );
		$this->assertContains( '/instant_callback', $response['url'], 'Expected a request to /instant_callbacks.' );
	}

	function testPostScheduledCallbacks() {
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$data = array(
			'start_timestamp' => time() + 10000,
			'recurrence_seconds' => 500,
			'metadata' => array( 'foo' => 'bar' ),
		);

		$response = $client->post_scheduled_callbacks( $data );

		$this->assertEquals( 'POST', $response['args']['method'] );
		$this->assertEquals( 'application/json', $response['args']['headers']['Content-Type'] );
		$this->assertEquals( json_encode( $data ), $response['args']['body'] );
		$this->assertContains( '/scheduled_callbacks', $response['url'] );
	}

	function testGetScheduledCallback() {
		$id = 4;
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->get_scheduled_callback( $id );

		$this->assertEquals( 'GET', $response['args']['method'] );
		$this->assertContains( '/scheduled_callbacks/4', $response['url'] );
	}

	function testDeleteScheduledCallback() {
		$id = 4;
		$client = new Prompt_Api_Client( array(), null, null, $this->mock );

		$response = $client->delete_scheduled_callback( $id );

		$this->assertEquals( 'DELETE', $response['args']['method'] );
		$this->assertContains( '/scheduled_callbacks/4', $response['url'] );
	}

}