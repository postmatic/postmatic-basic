<?php

class NewPostCommentCommandTest extends Prompt_MockMailerTestCase {

	function testAuthorUnsubscribe() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$subscriber_id = $this->factory->user->create();
		$this->mail_data->subscriber = get_userdata( $subscriber_id );
		$comment_text = 'unsubscribe';

		$message = new stdClass();
		$message->message = $comment_text;

		$prompt_author = new Prompt_User( $author_id );
		$prompt_author->subscribe( $subscriber_id );

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUnsubscribedEmail' ) );

		$command = new Prompt_New_Post_Comment_Command();
		$command->set_post_id( $post_id );
		$command->set_user_id( $subscriber_id );
		$command->set_message( $message );
		$command->execute();

		$this->assertFalse(
			$prompt_author->is_subscribed( $subscriber_id ),
			'Expected subscriber to be unsubscribed from author.'
		);
		$this->assertTrue(
			$prompt_site->is_subscribed( $subscriber_id ),
			'Expected subscriber to remain subscribed to site.'
		);

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $subscriber_id,
		) );
		$this->assertCount( 0, $comments, 'Expected no comments.' );
	}

	function testSiteUnsubscribe() {
		$post_id = $this->factory->post->create();
		$subscriber_id = $this->factory->user->create();
		$this->mail_data->subscriber = get_userdata( $subscriber_id );
		$comment_text = 'unsubscribe';

		$message = new stdClass();
		$message->message = $comment_text;

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUnsubscribedEmail' ) );

		$command = new Prompt_New_Post_Comment_Command();
		$command->set_post_id( $post_id );
		$command->set_user_id( $subscriber_id );
		$command->set_message( $message );
		$command->execute();

		$this->assertFalse(
			$prompt_site->is_subscribed( $subscriber_id ),
			'Expected subscriber to be unsubscribed from site.'
		);

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $subscriber_id,
		) );
		$this->assertCount( 0, $comments, 'Expected no comments.' );
	}

}
