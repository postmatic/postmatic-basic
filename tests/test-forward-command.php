<?php

Class PromptForwardCommandTest extends Prompt_MockMailerTestCase {

	function setUp() {
		parent::setUp();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	function makeCommand( $comment_text = null ) {
		$command = new Prompt_Forward_Command();
		$command->set_message( $comment_text );
		return $command;
	}

	function testKeys() {
		$test_keys = array( 'Prompt_Site', 0, 5, 239 );

		$command = $this->makeCommand();
		$command->set_keys( $test_keys );

		$this->assertEquals( $test_keys, $command->get_keys(), 'Expected to get the set keys back.' );
	}

	function testSetters() {
		$site = new Prompt_Site();
		$test_keys = array( get_class( $site ), $site->id(), 5, 239 );

		$command = $this->makeCommand();
		$command->set_subscription_object( $site );
		$command->set_from_user_id( $test_keys[2] );
		$command->set_to_user_id( $test_keys[3] );

		$this->assertEquals( $test_keys, $command->get_keys(), 'Expected to get the set keys back.' );
	}

	function testUnsubscribe() {
		$to_user_id = $this->factory->user->create();
		$this->mail_data->subscriber = $this->factory->user->create_and_get();
		$subscriber_id = $this->mail_data->subscriber->ID;
		$comment_text = ' unsubscribe';

		$message = new stdClass();
		$message->message = $comment_text;
		$message->subject = 'test subject';

		$list = new Prompt_Site();

		$list->subscribe( $subscriber_id );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUnsubscribedEmail' ) );

		$command = $this->makeCommand( $comment_text );
		$command->set_subscription_object( $list );
		$command->set_from_user_id( $subscriber_id );
		$command->set_to_user_id( $to_user_id );
		$command->set_message( $message );
		$command->execute();

		$this->assertFalse( $list->is_subscribed( $subscriber_id ), 'Expected user to be unsubscribed.' );
	}

	function testForward() {
		$author_id = $this->factory->user->create();
		$this->mail_data->author = get_userdata( $author_id );
		$commenter_id = $this->factory->user->create();
		$this->mail_data->commenter = get_userdata( $commenter_id );
		$comment_text = 'TEST COMMENT';

		$message = new stdClass();
		$message->message = $comment_text;
		$message->subject = $this->mail_data->subject = 'TEST SUBJECT';

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyForwardedEmail' ) );

		$command = $this->makeCommand( $comment_text );
		$command->set_subscription_object( new Prompt_Site() );
		$command->set_from_user_id( $commenter_id );
		$command->set_to_user_id( $author_id );
		$command->set_message( $message );
		$command->execute();
	}


	function testIgnore() {
		$author_id = $this->factory->user->create();
		$this->mail_data->author = get_userdata( $author_id );
		$commenter_id = $this->factory->user->create();
		$this->mail_data->commenter = get_userdata( $commenter_id );
		$comment_text = '  ';

		$list = new Prompt_Site();
		$message = new stdClass();
		$message->message = $comment_text;
		$message->subject = $this->mail_data->subject = 'TEST SUBJECT';

		$this->mailer_expects = $this->never();

		$command = $this->makeCommand( $comment_text );
		$command->set_subscription_object( $list );
		$command->set_from_user_id( $commenter_id );
		$command->set_to_user_id( $author_id );
		$command->set_message( $message );
		$command->execute();

		$this->assertFalse( $list->is_subscribed( $commenter_id ), 'Expected user to remain unsubscribed.' );
	}


	function verifyForwardedEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->author->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertEquals( $this->mail_data->commenter->display_name, $template['from_name'] );
		$this->assertEquals( $this->mail_data->subject, $template['subject'] );
	}

	function testWrongNumberOfKeysException() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$command = $this->makeCommand();
		$command->set_keys( array( 3 ) );
		$command->execute();
	}

}
