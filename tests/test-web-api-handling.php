<?php

class WebApiTest extends WP_Ajax_UnitTestCase {
	/** @var int */
	protected $_timestamp = null;
	/** @var string */
	protected $_token = null;
	/** @var string */
	protected $_signature = null;
	/** @var string */
	protected $_prompt_key = null;
	/** @var Prompt_Inbound_Messenger */
	protected $_mock_messenger = null;
	/** @var Prompt_Configurator */
	protected $_mock_configurator = null;
	/** @var stdClass  */
	protected $_data = null;

	function get_mock_messenger() {
		return $this->_mock_messenger;
	}

	function get_mock_configurator() {
		return $this->_mock_configurator;
	}

	function setUp() {
		parent::setUp();
		$this->_timestamp = time();
		$this->_token = md5( date( 'c' ) );
		$this->_prompt_key = '9ca1bc2515c5ddb67a7dbe038e994c9c';
		Prompt_Core::$options->set( 'prompt_key', $this->_prompt_key );
		$this->_signature = hash_hmac( 'sha256', $this->_timestamp . $this->_token, $this->_prompt_key );
		$this->_data = new stdClass();

		// Anticipate closed buffering and connection
		ob_start();

		add_filter( 'prompt/make_inbound_messenger', array( $this, 'get_mock_messenger' ) );
		add_filter( 'prompt/make_configurator', array( $this, 'get_mock_configurator' ) );

		// Trying to avoid triggering updates
		remove_filter( 'admin_init', '_maybe_update_core' );
		remove_filter( 'admin_init', '_maybe_update_themes' );
		remove_filter( 'admin_init', '_maybe_update_plugins' );
	}

	function tearDown() {
		parent::tearDown();
		Prompt_Core::$options->reset();
		remove_filter( 'prompt/make_inbound_messenger', array( $this, 'get_mock_messenger' ) );
		remove_filter( 'prompt/make_configurator', array( $this, 'get_mock_configurator' ) );
	}

	function testReceivePullUpdates() {
		$this->_mock_messenger = $this->getMock( 'Prompt_Inbound_Messenger' );
		$this->_mock_messenger->expects( $this->once() )
			->method( 'pull_updates' )
			->will( $this->returnValue( true ) );

		$_GET['timestamp'] = $this->_timestamp;
		$_GET['token'] = $this->_token;
		$_GET['signature'] = $this->_signature;

		try {
			$this->_handleAjax( 'nopriv_prompt/pull-updates' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}
	}

	function testReceivePullConfiguration() {
		$this->_mock_configurator = $this->getMock( 'Prompt_Configurator' );
		$this->_mock_configurator->expects( $this->once() )
			->method( 'pull_configuration' )
			->will( $this->returnValue( true ) );

		$_GET['timestamp'] = $this->_timestamp;
		$_GET['token'] = $this->_token;
		$_GET['signature'] = $this->_signature;

		try {
			$this->_handleAjax( 'nopriv_prompt/pull-configuration' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}
	}

	function testReceiveCallback() {

		$hook = 'prompt/test/callback';

		$metadata = array( $hook, array( 1, array( 'foo' => 'bar' ) ) );

		$_GET['timestamp'] = $this->_timestamp;
		$_GET['token'] = $this->_token;
		$_GET['signature'] = $this->_signature;
		$_GET['metadata'] = json_encode( $metadata );

		add_action( $hook, array( $this, 'verifyHook' ), 10, 2 );

		$this->_data->received_args = null;

		try {
			$this->_handleAjax( 'nopriv_prompt/instant-callback' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertEquals( $metadata[1], $this->_data->received_args, 'Expected to receive original arguments.' );

		remove_action( $hook, array( $this, 'hook' ), 10 );
	}

	function verifyHook() {
		$this->_data->received_args = func_get_args();
	}

	function testBadCallback() {
		$hook = 'test/callback';

		$_GET['timestamp'] = $this->_timestamp;
		$_GET['token'] = $this->_token;
		$_GET['signature'] = $this->_signature;
		$_GET['metadata'] = array( $hook, array( array( 'user_login' => 'test' ) ) );

		add_action( $hook, array( $this, 'verifyHook' ), 10, 2 );

		$this->_data->received_args = null;

		try {
			$this->_handleAjax( 'nopriv_prompt/instant-callback' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertNull( $this->_data->received_args, 'Expected bad hook not to be called.' );

		remove_action( $hook, array( $this, 'hook' ), 10 );
	}

	function testSetConnected() {

		Prompt_Core::$options->set( 'connection_status', '' );

		$_GET['timestamp'] = $this->_timestamp;
		$_GET['token'] = $this->_token;
		$_GET['signature'] = $this->_signature;
		$_GET['metadata'] = json_encode( array( 'prompt/test/callback', array() ) );

		try {
			$this->_handleAjax( 'nopriv_prompt/instant-callback' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertEquals(
			Prompt_Enum_Connection_Status::CONNECTED,
			Prompt_Core::$options->get( 'connection_status' ),
			'Expected conneciton status to be updated.'
		);

	}

	function testReceivePing() {

		// Ping doesn't close the output buffer
		ob_end_clean();

		$_GET['timestamp'] = $this->_timestamp;
		$_GET['token'] = $this->_token;
		$_GET['signature'] = $this->_signature;

		try {
			$this->_handleAjax( 'nopriv_prompt/ping' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$data = json_decode( $this->_last_response );

		$this->assertTrue( $data->success, 'Expected the standard WP success response.' );
	}


}