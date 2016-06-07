<?php

class InboundMessengerTest extends Prompt_MockMailerTestCase {
	/** @var WP_User */
	protected $commenter = null;
	/** @var WP_Post */
	protected $post = null;
	/** @var Prompt_Inbound_Messenger */
	protected $messenger = null;

	function setUp() {
		parent::setUp();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'localhost';
		$this->commenter = $this->factory->user->create_and_get();
		$this->post = $this->factory->post->create_and_get( array(
			'post_author' => $this->factory->user->create(),
		) );
		$this->messenger = Prompt_Factory::make_inbound_messenger();
	}

	function testInboundComment() {

		$restore_whitelist = get_option( 'comment_whitelist' );
		update_option( 'comment_whitelist', false );

		$command = new Prompt_Comment_Command();
		$command->set_post_id( $this->post->ID );
		$command->set_user_id( $this->commenter->ID );

		$update = new stdClass();
		$update->id = 'testid';
		$update->type = 'inbound-email';
		$update->status = 'accepted';
		$update->data = new stdClass();
		$update->data->from = 'from@test.dom';
		$update->data->message = <<<EOD
I'm an email comment. This is my text.

It has a couple of lines. The next text is quoted from the original email.
EOD;
		$update->data->metadata = new stdClass();
		$update->data->metadata->ids = array_merge( array( 1 ), $command->get_keys() );

		$this->mailer_expects = $this->never();

		$this->messenger->process_update( $update );

		$comments = get_approved_comments( $this->post->ID );
		$this->assertCount( 1, $comments, 'Comment wasn\'t added.' );
		$this->assertEquals( $this->commenter->ID, $comments[0]->user_id, 'Comment posted from the wrong user.' );
		$this->assertNotContains( 'INTRO TEXT', $comments[0]->comment_content, 'Quoted email not stripped from comment.' );

		update_option( 'comment_whitelist', $restore_whitelist );
	}

	function verifySubscribedEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->commenter->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( ' subscribed', $template['subject'] );
	}

	function testCommentSubscribe() {

		$command = new Prompt_Comment_Command();
		$command->set_post_id( $this->post->ID );
		$command->set_user_id( $this->commenter->ID );

		$update = new stdClass();
		$update->id = 'testid';
		$update->type = 'inbound-email';
		$update->status = 'accepted';
		$update->data = new stdClass();
		$update->data->from = 'from@test.dom';
		$update->data->message = 'subscribe';
		$update->data->metadata = new stdClass();
		$update->data->metadata->ids = array_merge( array( 1 ), $command->get_keys() );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifySubscribedEmail' ) );

		$this->messenger->process_update( $update );

		$prompt_post = new Prompt_Post( $this->post );
		$this->assertTrue( $prompt_post->is_subscribed( $this->commenter->ID ), 'Expected commenter to be subscribed.' );

		$comments = get_comments( array( 'post_id' => $prompt_post->id() ) );
		$this->assertEmpty( $comments, 'Expected no comments to be added.' );
	}

	function testCommentUnsubscribe() {
		$prompt_post = new Prompt_Post( $this->post );
		$prompt_post->subscribe( $this->commenter->ID );

		$command = new Prompt_Comment_Command();
		$command->set_post_id( $this->post->ID );
		$command->set_user_id( $this->commenter->ID );

		$update = new stdClass();
		$update->id = 'testid';
		$update->type = 'inbound-email';
		$update->status = 'accepted';
		$update->data = new stdClass();
		$update->data->from = 'from@test.dom';
		$update->data->message = "\nunsubscribe\n\nthanks a lot\n";
		$update->data->metadata = new stdClass();
		$update->data->metadata->ids = array_merge( array( 1 ), $command->get_keys() );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUnsubscribedEmail' ) );

		$this->messenger->process_update( $update );

		$this->assertFalse( $prompt_post->is_subscribed( $this->commenter->ID ), 'Expected commenter to be unsubscribed.' );
	}

	function verifyRegisterSubscribedEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->address, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( ' subscribed', $template['subject'] );
	}

	function testFunkyRegisterFormat() {

		$prompt_site = new Prompt_Site();
		$this->mail_data->address = 'test@example.com';
		
		$command = new Prompt_Register_Subscribe_Command();
		$command->save_subscription_data( 
			array( $prompt_site ), 
			$this->mail_data->address, 
			array( 'display_name' => 'Test Subscriber' ) 
		);

		// This test data came from a message (99107) that seemed to cause an error, but works here
		$update = new stdClass();
		$update->id = 'testid';
		$update->type = 'inbound-email';
		$update->status = 'accepted';
		$update->data = new stdClass();
		$update->data->from = 'prvs=38229u8321=Tester.TEST@example.com';
		$update->data->subject = 'Re: Test - Important: Confirm your subscription [SEC=UNCLASSIFIED]';
		$update->data->message = "agree";
		$update->data->metadata = new stdClass();
		$update->data->metadata->ids = array_merge( array( 2 ), $command->get_keys() );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyRegisterSubscribedEmail' ) );

		$this->messenger->process_update( $update );

		$new_user = get_user_by( 'email', 'test@example.com' );
		$this->assertNotEmpty( $new_user, 'Expected a new subscriber user to be created.' );
		$this->assertTrue( $prompt_site->is_subscribed( $new_user->ID ), 'Expected new user to be subscribed.' );
	}

	function verifyUnsubscribedEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->commenter->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( ' unsubscribed', $template['subject'] );
	}

	function testUnsubscribeError() {
		$prompt_post = new Prompt_Post( $this->post );
		$prompt_post->subscribe( $this->commenter->ID );

		$update = new stdClass();
		$update->id = 'testid';
		$update->type = 'inbound-email';
		$update->status = 'accepted';
		$update->data = new stdClass();
		$update->data->from = 'from@example.com';
		$update->data->message = "\nunsubscribe\n\nthanks a lot\n";
		$update->data->metadata = new stdClass();
		$bad_keys = array( 6, 0, $this->commenter->ID, 0, "Foo", 0 );
		$update->data->metadata->ids = $bad_keys;

		$this->mailer_expects = $this->never();

		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		
		$result = $this->messenger->process_update( $update );

		$this->assertEquals( 'failed', $result );
	}

	function testPullUpdates() {

		// Already subscribed commenter does not trigger a subscribed email
		$prompt_post = new Prompt_Post( $this->post );
		$prompt_post->subscribe( $this->commenter->ID );

		$command = new Prompt_Comment_Command();
		$command->set_post_id( $this->post->ID );
		$command->set_user_id( $this->commenter->ID );

		$response_body = array(
			'updates' => array(
				array(
					'id' => 'testid',
					'type' => 'inbound-email',
					'status' => 'accepted',
					'data' => array(
						'from' => 'from@test.dom',
						'message' => 'hello world',
						'metadata' => array( 'ids' => array_merge( array( 1 ), $command->get_keys() ) ),
					)
				)
			)
		);

		$response = array(
			'response' => array( 'code' => 200 ),
			'body' => json_encode( $response_body ),
		);

		$mock_client = $this->getMock( 'Prompt_Api_Client' );
		$mock_client->expects( $this->once() )
			->method( 'get_undelivered_updates' )
			->will( $this->returnValue( $response ) );

		$messenger = new Prompt_Inbound_Messenger( $mock_client );
		$messenger->pull_updates();
	}

	function testAcknowledgeUpdates() {

		$updated_results_body = array(
			'updates' => array(
				array(
					'id' => 'testid',
					'status' => 'delivered'
				)
			)
		);

		$put_request = array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => json_encode( $updated_results_body )
		);

		$put_response = array(
			'response' => array( 'code' => 200 ),
			'body' => json_encode( $updated_results_body )
		);

		$mock_client = $this->getMock( 'Prompt_Api_Client' );
		$mock_client->expects( $this->once() )
			->method( 'put' )
			->with( '/updates', $put_request )
			->will( $this->returnValue( $put_response ) );

		$messenger = new Prompt_Inbound_Messenger( $mock_client );
		$result = $messenger->acknowledge_updates( $updated_results_body );

		$this->assertTrue( $result, 'Expected successful acknowledgement.' );
	}

	function testAcknowledgeEmpty() {
		$empty_updates = array( 'updates' => array() );

		$mock_client = $this->getMock( 'Prompt_Api_Client' );
		$mock_client->expects( $this->never() )->method( 'put' );

		$messenger = new Prompt_Inbound_Messenger( $mock_client );
		$result = $messenger->acknowledge_updates( $empty_updates );

		$this->assertTrue( $result, 'Expected success with no request necessary.' );
	}
}