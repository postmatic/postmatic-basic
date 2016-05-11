<?php

class PromptCommentFloodCommandTest extends Prompt_MockMailerTestCase {

	function testRejoin() {
		$post_id = $this->factory->post->create();
		$this->mail_data->commenter = $this->factory->user->create_and_get();
		$comment_text = Prompt_Rejoin_Matcher::target();

		$message = new stdClass();
		$message->message = $comment_text;

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyRejoinEmail' ) );

		$command = new Prompt_Comment_Flood_Command();
		$command->set_keys( array( $post_id, $this->mail_data->commenter->ID ) );
		$command->set_message( $message );
		$command->execute();

		$prompt_post = new Prompt_Post( $post_id );

		$this->assertTrue( $prompt_post->is_subscribed( $this->mail_data->commenter->ID ), 'Expected user to be subscribed.' );

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $this->mail_data->commenter->ID,
		) );
		$this->assertCount( 0, $comments, 'Expected no comments.' );
	}

	function verifyRejoinEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->commenter->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( ' rejoined', $template['subject'] );
	}

	function testCommentSuppression() {

		$this->mailer_expects = $this->never();

		$post_id = $this->factory->post->create();
		$commenter_id = $this->factory->user->create();

		$comment_text = 'Test comment.';
		$message = new stdClass();
		$message->message = $comment_text;

		$command = new Prompt_Comment_Flood_Command();
		$command->set_keys( array( $post_id, $commenter_id ) );
		$command->set_message( $message );
		$command->execute();

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $commenter_id,
		) );
		$this->assertCount( 0, $comments, 'Expected no comments.' );

	}
}
