<?php

class PromptAjaxTest extends WP_Ajax_UnitTestCase {

	/** @var WP_User */
	protected $_author = null;
	/** @var WP_Post */
	protected $_post = null;
	/** @var Prompt_Email_Batch */
	protected $_mailer_payload = null;
	protected $_mailer_expects;
	protected $_mailer_will;

	public function setUp() {
		parent::setUp();
		$this->_author = $this->factory->user->create_and_get();
		$this->_post = $this->factory->post->create_and_get( array(
			'post_author' => $this->_author->ID,
		) );

		$this->_mailer_expects = $this->once();

		add_filter( 'prompt/make_mailer', array( $this, 'get_mock_mailer' ), 10, 3 );

		// Trying to avoid triggering updates
		remove_filter( 'admin_init', '_maybe_update_core' );
		remove_filter( 'admin_init', '_maybe_update_themes' );
		remove_filter( 'admin_init', '_maybe_update_plugins' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'prompt/make_mailer', array( $this, 'get_mock_mailer' ) );

		// Exception cases leave an output buffer on
		if ( ob_get_level() > 1 )
			ob_end_flush();
	}

	public function testCommentUnsubscribe() {
		$commenter_id = $this->factory->user->create();
		$post_id = $this->factory->post->create();

		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $commenter_id );

		wp_set_current_user( $commenter_id );

		$_POST['nonce'] = wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE );
		$_POST['post_id'] = $post_id;

		try {
			$this->_handleAjax( Prompt_Comment_Form_Handling::UNSUBSCRIBE_ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertFalse( $prompt_post->is_subscribed( $commenter_id ) );
	}

	public function testIsConnected() {

		Prompt_Core::$options->set( 'connection_status', Prompt_Enum_Connection_Status::CONNECTED );

		try {
			$this->_handleAjax( 'prompt_is_connected' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$data = json_decode( $this->_last_response );

		$this->assertTrue( $data->success, 'Expected the success attribute to be true.' );
		$this->assertTrue( $data->data, 'Expected the data attribute to be true.' );

		Prompt_Core::$options->reset();
	}

	public function testNotConnected() {

		Prompt_Core::$options->set( 'connection_status', Prompt_Enum_Connection_Status::UNREACHABLE );

		try {
			$this->_handleAjax( 'prompt_is_connected' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$data = json_decode( $this->_last_response );

		$this->assertTrue( $data->success, 'Expected the success attribute to be true.' );
		$this->assertFalse( $data->data, 'Expected the data attribute to be false.' );

		Prompt_Core::$options->reset();
	}

	public function get_mock_mailer( $mailer, $payload, $transport ) {
		$this->_mailer_payload = $payload;

		$mock = $this->getMock( 'Prompt_Mailer', array( 'send' ), array( $payload ) );
		$mock->expects( $this->_mailer_expects )
			->method( 'send' )
			->will( $this->_mailer_will );

		return $mock;
	}
}