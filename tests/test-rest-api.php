<?php

/** @group rest */
class RestApiTest extends WP_UnitTestCase {

	/** @var Prompt_Rest_Api */
	protected $rest_api;
	/** @var WP_REST_Server */
	protected $server;

	public function setUp() {
		// Override the normal server with our spying server.
		$this->server = $GLOBALS['wp_rest_server'] = new WP_REST_Server();
		$this->rest_api = Prompt_Rest_Api::get_instance( array( 'reset' => true ) );
		parent::setUp();
	}
	
	public function tearDown() {
		parent::tearDown(); 
		$GLOBALS['wp_rest_server'] = null;
	}

	function test_instantiation() {
		$this->assertInstanceOf( 'Prompt_Rest_Api', $this->rest_api );
	}

	function test_registered_routes() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey(
			'/postmatic/v1/invocations',
			$routes,
			'Expected invocations route to be registered.'
		);

		$invocations = $routes['/postmatic/v1/invocations'];

		$this->assertArrayHasKey(
			WP_REST_Server::CREATABLE,
			$invocations[0]['methods'],
			'Expected a POST invocations method.'
		);
		$this->assertEquals(
			array( $this->rest_api, 'create_invocation' ),
			$invocations[0]['callback'],
			'Expected a callback to the create invocation method.'
		);
	}

	function test_is_signed() {

		$signature = new Prompt_Request_Signature( \Prompt_Core::$options->get( 'prompt_key' ) );

		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'timestamp', $signature->get_timestamp() );
		$request->set_param( 'token', $signature->get_token() );
		$request->set_param( 'signature', $signature->get_signature() );

		$permission = $this->rest_api->is_signed( $request );

		$this->assertTrue( $permission, 'Expected permission to be granted.' );
	}

	function test_is_signed_error() {

		$signature = new Prompt_Request_Signature( \Prompt_Core::$options->get( 'prompt_key' ) );

		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'timestamp', $signature->get_timestamp() );
		$request->set_param( 'token', $signature->get_token() );
		$request->set_param( 'signature', 'foo' );

		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$permission = $this->rest_api->is_signed( $request );

		$this->assertInstanceOf( 'WP_Error', $permission );

		$error_data = $permission->get_error_data();
		$this->assertEquals( 401, $error_data['status'] );
	}

	function test_create_invocation() {

		$metadata = array(
			'prompt/test',
			array( 'foo', 'bar' ),
		);

		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'metadata', $metadata );

		$action_mock = $this->getMock( 'Action_Mock', array( 'invoke' ) );
		$action_mock->expects( $this->once() )
			->method( 'invoke' )
			->with( 'foo', 'bar' );

		add_action( 'prompt/test', array( $action_mock, 'invoke' ), 10, 2 );

		$response = $this->rest_api->create_invocation( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 204, $response->get_status(), 'Expected 204 status.' );

		remove_action( 'prompt/test', array( $action_mock, 'invoke' ) );
	}

	function test_sanitize_invocation_metadata() {
		$metadata = array(
			'prompt/test',
			array( 'foo', 'bar' ),
		);

		$sanitized_metadata = $this->rest_api->sanitize_invocation_metadata( json_encode( $metadata ), null, 'metadata' );

		$this->assertEquals( $metadata, $sanitized_metadata );
	}

	function test_validate_invocation_metadata_with_valid_data() {

		$metadata = array(
			'prompt/test',
			array( 'foo', 'bar' ),
		);

		$validity = $this->rest_api->validate_invocation_metadata( $metadata, null, 'metadata' );

		$this->assertTrue( $validity, 'Expected metadata to be valid.' );
	}

	function test_validate_invocation_metadata_with_disallowed_hook() {

		$metadata = array(
			'set_current_user',
			array( 'foo', 'bar' ),
		);

		$validity = $this->rest_api->validate_invocation_metadata( $metadata, null, 'metadata' );

		$this->assertFalse( $validity, 'Expected metadata NOT to be valid.' );
	}

	function test_unauthorized_invocation_post() {
		
		$metadata = array(
			'prompt/test',
			array( 'foo', 'bar' ),
		);
		
		$request = new WP_REST_Request( 'POST', '/postmatic/v1/invocations' );
		$request->set_param( 'metadata', json_encode( $metadata ) );

		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 401, $response->get_status() );
	}

	function test_invocation_post() {
		
		$signature = new Prompt_Request_Signature( Prompt_Core::$options->get( 'prompt_key' ) );
		
		$metadata = array(
			'prompt/test',
			array( 'foo' ),
		);

		$hook_mock = $this->getMock( 'AdHoc', array( 'test' ) );
		$hook_mock->expects( $this->once() )
			->method( 'test' )
			->with( 'foo' );
		
		add_action( 'prompt/test', array( $hook_mock, 'test' ) );
		
		$request = new WP_REST_Request( 'POST', '/postmatic/v1/invocations' );
		$request->set_param( 'metadata', json_encode( $metadata ) );
		$request->set_param( 'timestamp', $signature->get_timestamp() );
		$request->set_param( 'token', $signature->get_token() );
		$request->set_param( 'signature', $signature->get_signature() );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 204, $response->get_status() );
		
		remove_action( 'prompt/test', array( $hook_mock, 'test' ) );
	}
	
}