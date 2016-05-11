<?php

class ConfirmationCommandTest extends Prompt_MockMailerTestCase {

	function verifyUnsubscribeEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->subscriber->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( ' unsubscribed', $template['subject'] );
	}

	function testAuthorUnsubscribe() {
		$author_id = $this->factory->user->create();
		$subscriber_id = $this->factory->user->create();
		$this->mail_data->subscriber = get_userdata( $subscriber_id );
		$comment_text = 'unsubscribe';

		$message = new stdClass();
		$message->message = $comment_text;

		$prompt_author = new Prompt_User( $author_id );
		$prompt_author->subscribe( $subscriber_id );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUnsubscribeEmail' ) );

		$command = new Prompt_Confirmation_Command();
		$command->set_post_id( 0 );
		$command->set_user_id( $subscriber_id );
		$command->set_object_type( 'Prompt_User' );
		$command->set_object_id( $author_id );
		$command->set_message( $message );
		$command->execute();

		$this->assertFalse(
			$prompt_author->is_subscribed( $subscriber_id ),
			'Expected subscriber to be unsubscribed from author.'
		);

	}

	function testIgnoredMessage() {
		$subscriber_id = $this->factory->user->create();
		$this->mail_data->subscriber = get_userdata( $subscriber_id );
		$comment_text = 'random blabbering';

		$message = new stdClass();
		$message->message = $comment_text;

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$this->mailer_expects = $this->never();

		$command = new Prompt_Confirmation_Command();
		$command->set_post_id( 0 );
		$command->set_user_id( $subscriber_id );
		$command->set_object_type( 'Prompt_Site' );
		$command->set_object_id( $prompt_site->id() );
		$command->set_message( $message );
		$command->execute();

		$this->assertTrue(
			$prompt_site->is_subscribed( $subscriber_id ),
			'Expected subscriber to remain subscribed to site.'
		);

	}

}
