<?php

Class PromptPostRequestCommandTest extends Prompt_MockMailerTestCase {

	function setUp() {
		parent::setUp();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	function testKeys() {
		$test_keys = array( 5, 239 );

		$command = new Prompt_Post_Request_Command();

		$command->set_keys( $test_keys );

		$this->assertEquals( $test_keys, $command->get_keys(), 'Expected to get the set keys back.' );
	}

	function testSetters() {
		$test_keys = array( 5, 239 );

		$command = new Prompt_Post_Request_Command();
		$command->set_post_id( $test_keys[0] );
		$command->set_user_id( $test_keys[1] );

		$this->assertEquals( $test_keys, $command->get_keys(), 'Expected to get the set keys back.' );
	}

	function testSendPost() {
		$author_id = $this->factory->user->create( array( 'role' => 'author' ) );
		$post_id = $this->factory->post->create( array( 'post_content' => 'XXCONTENTXX', 'post_author' => $author_id ) );
		update_post_meta( $post_id, 'prompt_excerpt_only', 1 );

		$this->mail_data->post = get_post( $post_id );
		$recipient_id = $this->factory->user->create();
		$this->mail_data->recipient = get_userdata( $recipient_id );

		$message = new stdClass();
		$message->message = 'IGNORED MESSAGE CONTENT';

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyPostEmail' ) );

		$command = new Prompt_Post_Request_Command();
		$command->set_post_id( $post_id );
		$command->set_user_id( $recipient_id );
		$command->set_message( $message );
		$command->execute();
	}

	function verifyPostEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->recipient->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( $this->mail_data->post->post_title, $template['subject'] );
		$this->assertContains(
			$this->mail_data->post->post_content,
			$template['html_content'],
			'Expected post content in email.'
		);
	}

	function testWrongNumberOfKeysException() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$command = new Prompt_Post_Request_Command();
		$command->set_keys( array( 3 ) );
		$command->execute();
	}

}
