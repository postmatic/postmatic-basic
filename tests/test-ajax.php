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

	public function testAsAdministrator() {
		$this->_setRole( 'administrator' );

		$this->successfulSubscriptionCases();
	}

	public function testAsSubscriber() {
		$this->_setRole( 'subscriber' );

		$this->successfulSubscriptionCases();
	}

	private function successfulSubscriptionCases() {
		$subscription_objects = array(
			new Prompt_Site,
			new Prompt_Post( $this->_post ),
			new Prompt_User( $this->_author ),
		);
		$subscriber = wp_get_current_user();

		foreach ( $subscription_objects as $object ) {

			$this->_mailer_expects = $this->exactly( 2 );
			$this->_mailer_will = $this->returnCallback( array( $this, 'verify_subscribe_email' ) );

			$_POST['subscribe_nonce'] = wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE );
			$_POST['subscribe_topic'] = '';
			$_POST['object_type'] = $object ? get_class( $object ) : '';
			$_POST['object_id'] = $object ? $object->id() : '';
			$_POST['mode'] = 'subscribe';

			try {
				$this->_last_response = '';
				$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
			} catch ( WPAjaxDieContinueException $e ) {
				unset( $e );
			}

			$this->assertNotEmpty( $this->_last_response );
			$this->assertNotContains( 'Notice:', $this->_last_response );
			$this->assertNotContains( 'Error:', $this->_last_response );
			$this->assertTrue(
				$object->is_subscribed( $subscriber->ID ),
				'Expected successful subscription by ' . get_class( $object )
			);

			// A second submission should have the same result
			try {
				$this->_last_response = '';
				$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
			} catch ( WPAjaxDieContinueException $e ) {
				unset( $e );
			}

			$this->assertNotEmpty( $this->_last_response );
			$this->assertNotContains( 'Notice:', $this->_last_response );
			$this->assertNotContains( 'Error:', $this->_last_response );
			$this->assertTrue(
				$object->is_subscribed( $subscriber->ID ),
				'Expected successful subscription by ' . get_class( $object )
			);

			$this->_mailer_will = $this->returnCallback( array( $this, 'verify_unsubscribe_email' ) );

			$_POST['mode'] = 'unsubscribe';

			try {
				$this->_last_response = '';
				$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
			} catch ( WPAjaxDieContinueException $e ) {
				unset( $e );
			}

			$this->assertNotEmpty( $this->_last_response );
			$this->assertNotContains( 'Notice:', $this->_last_response );
			$this->assertNotContains( 'Error:', $this->_last_response );
			$this->assertFalse(
				$object->is_subscribed( get_current_user_id() ),
				'Unsubscription failed.'
			);

		}
	}

	public function verify_subscribe_email() {
		$template = $this->_mailer_payload->get_batch_message_template();
		$this->assertContains( 'subscribe', $template['subject'] );
		$this->assertNotContains( 'unsubscribe', $template['subject'] );
		return false;
	}

	public function verify_unsubscribe_email() {
		$template = $this->_mailer_payload->get_batch_message_template();
		$this->assertContains( 'unsubscribe', $template['subject'] );
		return false;
	}

	public function testNewUser() {

		wp_logout();

		$test_email = 'testuser@prompt.vern.al';
		$_POST['subscribe_nonce'] = wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE );
		$_POST['subscribe_topic'] = '';
		$_POST['object_type'] = 'Prompt_Post';
		$_POST['subscribe_name'] = '';
		$_POST['subscribe_email'] = $test_email;
		$_POST['object_id'] = $this->_post->ID;
		$_POST['subscribe_submit'] = 'subscribe';

		$this->_mailer_will = $this->returnCallback( array( $this, 'verify_new_user_email' ) );

		try {
			$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotEmpty( $this->_last_response );
		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );

		$new_user = get_user_by( 'email', $test_email );
		$this->assertEmpty( $new_user, 'Did not expect the new user to be created yet.' );

		$prompt_post = new Prompt_Post( $this->_post );
		$this->assertCount( 0, $prompt_post->subscriber_ids(), 'Expected the post to have no subscribers.' );

	}

	public function verify_new_user_email() {
		$template = $this->_mailer_payload->get_batch_message_template();
		$this->assertContains( 'confirm', $template['subject'] );
		return false;
	}

	public function testNewUserNoList() {

		wp_logout();

		$test_email = 'test@example.com';
		$_POST['subscribe_nonce'] = wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE );
		$_POST['subscribe_topic'] = '';
		$_POST['subscribe_name'] = 'TEST';
		$_POST['subscribe_email'] = $test_email;
		$_POST['subscribe_submit'] = 'subscribe';

		$this->_mailer_will = $this->returnCallback( array( $this, 'verify_new_user_email' ) );

		try {
			$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotEmpty( $this->_last_response );
		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );

		$new_user = get_user_by( 'email', $test_email );
		$this->assertEmpty( $new_user, 'Did not expect the new user to be created yet.' );

	}

	public function testLoggedInSubscribedUser() {

		$user = $this->factory->user->create_and_get();

		$prompt_site = new Prompt_Site();

		$prompt_site->subscribe( $user->ID );

		wp_set_current_user( $user->ID );

		$_POST['subscribe_nonce'] = wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE );
		$_POST['subscribe_topic'] = '';
		$_POST['subscribe_submit'] = 'subscribe';

		$this->_mailer_expects = $this->never();

		try {
			$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );
		$this->assertContains( 'already', $this->_last_response );
		$this->assertContains(
			$prompt_site->subscription_object_label(),
			$this->_last_response,
			'Expected the new posts list label in the widget response.'
		);

		$this->assertTrue( $prompt_site->is_subscribed( $user->ID ), 'Expected the user to still be subscribed.' );

	}

	public function testLoggedOutSubscribedUser() {

		$user = $this->factory->user->create_and_get();

		$prompt_site = new Prompt_Site();

		$prompt_site->subscribe( $user->ID );

		wp_logout();

		$_POST['subscribe_nonce'] = wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE );
		$_POST['subscribe_topic'] = '';
		$_POST['object_type'] = 'Prompt_Site';
		$_POST['subscribe_name'] = 'TEST';
		$_POST['subscribe_email'] = $user->user_email;
		$_POST['object_id'] = $prompt_site->id();
		$_POST['subscribe_submit'] = 'subscribe';

		$this->_mailer_expects = $this->never();

		try {
			$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotEmpty( $this->_last_response );
		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );
		$this->assertContains( 'already', $this->_last_response );

		$this->assertTrue( $prompt_site->is_subscribed( $user->ID ), 'Expected the user to still be subscribed.' );

	}

	public function testBadNonce() {
		$_POST['subscribe_nonce'] = 'wrong';
		$_POST['subscribe_topic'] = '';
		$_POST['object_type'] = 'Prompt_User';
		$_POST['object_id'] = $this->_author;
		$_POST['subscribe_submit'] = 'subscribe';

		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
	}

	public function testBadTopic() {
		$_POST['subscribe_nonce'] = wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE );
		$_POST['subscribe_topic'] = 'gotcha';
		$_POST['object_type'] = 'Prompt_User';
		$_POST['object_id'] = $this->_author;
		$_POST['subscribe_submit'] = 'subscribe';

		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$this->_handleAjax( Prompt_Subscribing::SUBSCRIBE_ACTION );
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

	public function testPreview() {

		$_GET['post_id'] = $this->_post->ID;

		$this->_mailer_will = $this->returnCallback( array( $this, 'verifyPreviewEmail' ) );

		try {
			$this->_handleAjax( 'prompt_post_delivery_preview' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotEmpty( $this->_last_response );
		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );

		$this->assertContains( 'Preview email sent', $this->_last_response );

	}

	public function verifyPreviewEmail() {
		$current_user = wp_get_current_user();
		$values = $this->_mailer_payload->get_individual_message_values();
		$this->assertEquals( $current_user->user_email, $values[0]['to_address'] );

		$template = $this->_mailer_payload->get_batch_message_template();
		$this->assertContains( $this->_post->post_title, $template['subject'] );
		$this->assertContains( $this->_post->post_content, $template['html_content'] );
		return false;
	}

	public function testSubscribeWidgetContent() {
		$_GET['widget_id'] = '-1';
		$_GET['template'] = '';
		$_GET['collect_name'] = '1';

		try {
			$this->_handleAjax( 'prompt_subscribe_widget_content' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotEmpty( $this->_last_response );
		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );
		$this->assertContains( '<form', $this->_last_response, 'Expected a form in the widget content.' );
	}

	public function testUnsubscribeWidgetContent() {
		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$site = new Prompt_Site();
		$site->subscribe( $user_id );

		$_GET['widget_id'] = '-1';
		$_GET['list_type'] = 'Prompt_Site';
		$_GET['list_id'] = $site->id();

		try {
			$this->_handleAjax( 'prompt_subscribe_widget_content' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotEmpty( $this->_last_response );
		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );
		$this->assertContains( 'value="unsubscribe"', $this->_last_response, 'Expected an unsubscribe mode value.' );

		wp_set_current_user( 0 );
	}

	public function testSubscribeWidgetListContent() {
		$user_id = $this->factory->user->create();
		$_GET['widget_id'] = '-1';
		$_GET['list_type'] = 'Prompt_User';
		$_GET['list_id'] = $user_id;

		try {
			$this->_handleAjax( 'prompt_subscribe_widget_content' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertNotEmpty( $this->_last_response );
		$this->assertNotContains( 'Notice:', $this->_last_response );
		$this->assertNotContains( 'Error:', $this->_last_response );
		$this->assertContains(
			'value="Prompt_User"',
			$this->_last_response,
			'Expected the list type in the widget content.'
		);
		$this->assertContains(
			'value="' . $user_id . '"',
			$this->_last_response,
			'Expected the list id in the widget content.'
		);
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