<?php

class CommentMailingTest extends Prompt_MockMailerTestCase {

	protected $user;
	protected $post;

	public function setUp() {
		parent::setUp();

		$this->post = $this->factory->post->create_and_get();
		$this->user = $this->factory->user->create_and_get();

	}

	function testSendRejectedNotification() {
		$this->mailer_will = $this->returnCallback( array( $this, 'verifyRejectedNotification' ) );
		Prompt_Comment_Mailing::send_rejected_notification( $this->user->ID, $this->post->ID );
	}

	function verifyRejectedNotification() {
		$message_template = $this->mailer_payload->get_batch_message_template();
		$message_values = $this->mailer_payload->get_individual_message_values();

		$this->assertEquals(
			$this->user->user_email,
			$message_values[0]['to_address'],
			'Expected email to commenter only.'
		);

		$this->assertContains(
			$this->post->post_title,
			$message_template['subject'],
			'Expected subject to contain post title.'
		);

		$this->assertEquals(
			Prompt_Enum_Message_Types::ADMIN,
			$message_template['message_type'],
			'Expected admin message type.'
		);
		return true;
	}

	function testSendRejectedNotificationForNonExistentPost() {
		$non_existent_post_id = -1;
		$this->mailer_will = $this->returnCallback( array( $this, 'verifyRejectedNotificationForNonExistentPost' ) );
		Prompt_Comment_Mailing::send_rejected_notification( $this->user->ID, $non_existent_post_id );
	}

	function verifyRejectedNotificationForNonExistentPost() {
		$message_template = $this->mailer_payload->get_batch_message_template();

		$this->assertContains(
			'deleted post',
			$message_template['subject'],
			'Expected subject to contain "deleted post".'
		);

		return true;
	}

	function testCommentAddSubscription() {

		$prompt_post = new Prompt_Post( $this->factory->post->create() );

		$this->mailer_expects = $this->exactly( 2 ); // one for the new subscriber, one for comment notifications
		$this->mailer_will = $this->returnCallback( array( $this, 'verifyCommentSubscriberNotification' ) );

		$this->mail_data->comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $prompt_post->id(),
			'comment_author_email' => 'addsub@example.com',
		 ) );

		// Mock a stored response to the comment subscription form
		add_comment_meta( $this->mail_data->comment->comment_ID, Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME, 1 );

		Prompt_Comment_Mailing::send_notifications( $this->mail_data->comment->comment_ID );

		$this->assertEmpty(
			get_user_by( 'email', $this->mail_data->comment->comment_author_email ),
			'Expected no user with the commenter email address.'
		);

	}

	function verifyCommentSubscriberNotification() {
		if ( is_a( $this->mailer_payload, 'Prompt_Comment_Email_Batch' ) ) {
			// we're testing the subscription mailing
			return;
		}
		$message_template = $this->mailer_payload->get_batch_message_template();
		$message_values = $this->mailer_payload->get_individual_message_values();

		$this->assertEquals( $this->mail_data->comment->comment_author_email, $message_values[0]['to_address'] );
		$this->assertEquals(
			Prompt_Enum_Message_Types::SUBSCRIPTION,
			$message_template['message_type'],
			'Expected admin message type.'
		);
	}

}