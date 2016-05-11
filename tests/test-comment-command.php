<?php

Class PromptCommentCommandTest extends Prompt_MockMailerTestCase {

	function setUp() {
		parent::setUp();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	function makeCommandWithMessage( $comment_text = null ) {
		$message = new stdClass();
		$message->message = $comment_text;

		$command = new Prompt_Comment_Command();
		$command->set_message( $message );
		return $command;
	}

	function testKeys() {
		$test_keys = array( 3, 5, 239 );

		$command = $this->makeCommandWithMessage();
		$command->set_keys( $test_keys );

		$this->assertEquals( $test_keys, $command->get_keys(), 'Expected to get the set keys back.' );
	}

	function testIdSetters() {
		$test_keys = array( 3, 5, 239 );

		$command = $this->makeCommandWithMessage();
		$command->set_post_id( $test_keys[0] );
		$command->set_user_id( $test_keys[1] );
		$command->set_parent_comment_id( $test_keys[2] );

		$this->assertEquals( $test_keys, $command->get_keys(), 'Expected to get the set keys back.' );
	}

	/**
	 * @dataProvider subscribeCommandProvider
	 */
	function testSubscribe( $command ) {
		$post_id = $this->factory->post->create();
		$this->mail_data->subscriber = $this->factory->user->create_and_get();
		$comment_text = $command;

		$this->mailer_will = $this->returnCallback( array( $this, 'verifySubscribedEmail' ) );

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $this->mail_data->subscriber->ID ) );
		$command->execute();

		$prompt_post = new Prompt_Post( $post_id );

		$this->assertTrue( $prompt_post->is_subscribed( $this->mail_data->subscriber->ID ), 'Expected user to be subscribed.' );

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $this->mail_data->subscriber->ID,
		) );
		$this->assertCount( 0, $comments, 'Expected no comments.' );
	}

	function subscribeCommandProvider() {
		return array(
			array( 'subscribe' ),
			array( "\t" ),
			array( '' ),
		);
	}

	function testUnsubscribe() {
		$post_id = $this->factory->post->create();
		$this->mail_data->commenter = $this->factory->user->create_and_get();
		$commenter_id = $this->mail_data->commenter->ID;
		$comment_text = 'unsubscribe';

		$message = new stdClass();
		$message->message = $comment_text;

		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $commenter_id );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUnsubscribedEmail' ) );

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $commenter_id, 0 ) );
		$command->set_message( $message );
		$command->execute();

		$this->assertFalse( $prompt_post->is_subscribed( $commenter_id ), 'Expected user to be unsubscribed.' );

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $commenter_id,
		) );
		$this->assertCount( 0, $comments, 'Expected no comments.' );
	}

	function verifyUnsubscribedEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->commenter->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( ' unsubscribed', $template['subject'] );
	}

	function testDoubleSubscribe() {
		$post_id = $this->factory->post->create();
		$commenter_id = $this->factory->user->create();
		$comment_text = 'subscribe';

		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $commenter_id );

		$message = new stdClass();
		$message->message = $comment_text;

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $commenter_id, 0 ) );
		$command->set_message( $message );
		$command->execute();

		$this->assertTrue( $prompt_post->is_subscribed( $commenter_id ), 'Expected user to be subscribed.' );

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $commenter_id,
		) );
		$this->assertCount( 0, $comments, 'Expected no comments.' );
	}

	function testAddComment() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$commenter_id = $this->factory->user->create();
		$this->mail_data->commenter = get_userdata( $commenter_id );
		$comment_text = 'TEST COMMENT';

		$message = new stdClass();
		$message->message = $comment_text;

		$this->mailer_expects = $this->never();

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $commenter_id, 0 ) );
		$command->set_message( $message );
		$command->execute();

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $commenter_id,
		) );

		$this->assertCount( 1, $comments, 'Expected to find new comment.' );
		$this->assertEquals(
			$comment_text,
			$comments[0]->comment_content,
			'Expected the comment text to be the same as the message body.'
		);

		$prompt_post = new Prompt_Post( $post_id );
		$this->assertTrue( $prompt_post->is_subscribed( $commenter_id ), 'Expected the commenter to be subscribed to post.' );
	}

	function testModerateComment() {
		$restore_comment_moderation = get_option( 'comment_moderation' );
		update_option( 'comment_moderation', 1 );

		remove_filter(
			'comment_moderation_recipients',
			array( 'Prompt_Outbound_Handling', 'filter_comment_moderation_recipients' ),
			10
		);
		add_filter( 'comment_moderation_recipients', array( $this, 'verifyModerationRecipients' ), 10, 2 );

		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$commenter_id = $this->factory->user->create();
		$this->mail_data->moderation_sent = false;
		$comment_text = 'TEST COMMENT';

		$message = new stdClass();
		$message->message = $comment_text;

		$this->mailer_expects = $this->never();

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $commenter_id, 0 ) );
		$command->set_message( $message );
		$command->execute();

		$this->assertTrue( $this->mail_data->moderation_sent, 'Expected moderation email to be triggered.' );

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $commenter_id,
		) );

		$this->assertCount( 1, $comments, 'Expected to find new comment.' );
		$this->assertEquals(
			0,
			$comments[0]->comment_approved,
			'Expected the comment to be moderated.'
		);

		$prompt_post = new Prompt_Post( $post_id );
		$this->assertTrue(
			$prompt_post->is_subscribed( $commenter_id ),
			'Expected the commenter to be subscribed to post.'
		);

		remove_filter( 'comment_moderation_recipients', array( $this, 'verifyModerationRecipients' ), 10 );
		add_filter(
			'comment_moderation_recipients',
			array( 'Prompt_Outbound_Handling', 'filter_comment_moderation_recipients' ),
			10,
			2
		);
		update_option( 'comment_moderation', $restore_comment_moderation );
	}

	function verifyModerationRecipients( $addresses, $comment_id ) {

		$this->assertCount( 1, $addresses, 'Expected one moderation email.' );
		$this->mail_data->moderation_sent = true;

		return array(); // Prevent actual emailing
	}

	function testAddChildComment() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );

		$parent_comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $post_id ) );

		$commenter_id = $this->factory->user->create();
		$this->mail_data->commenter = get_userdata( $commenter_id );
		$comment_text = 'TEST COMMENT REPLY';

		$message = new stdClass();
		$message->message = $comment_text;

		$this->mailer_expects = $this->never();

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $commenter_id, $parent_comment_id ) );
		$command->set_message( $message );
		$command->execute();

		$comments = get_comments( array(
			'post_id' => $post_id,
			'orderby' => 'comment_date_gmt',
			'order' => 'ASC',
		) );

		$this->assertCount( 2, $comments, 'Expected two comments.' );
		$this->assertEquals(
			$comment_text,
			$comments[1]->comment_content,
			'Expected the reply comment text to be the same as the message body.'
		);
		$this->assertEquals(
			$parent_comment_id,
			$comments[1]->comment_parent,
			'Expected the comment parent to match the original.'
		);

		$prompt_post = new Prompt_Post( $post_id );
		$this->assertTrue( $prompt_post->is_subscribed( $commenter_id ), 'Expected the commenter to be subscribed to post.' );
	}

	function testAuthorComment() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$comment_text = 'TEST COMMENT';

		$message = new stdClass();
		$message->message = $comment_text;

		$this->mailer_expects = $this->never();

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $author_id, 0 ) );
		$command->set_message( $message );
		$command->execute();

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $author_id,
		) );

		$this->assertCount( 1, $comments, 'Expected to find new comment.' );
		$this->assertEquals(
			$comment_text,
			$comments[0]->comment_content,
			'Expected the comment text to be the same as the message body.'
		);

		$prompt_post = new Prompt_Post( $post_id );
		$this->assertFalse(
			$prompt_post->is_subscribed( $author_id ),
			'Expected the author, already auto-subscribed, NOT to be subscribed to post.'
		);
	}

	function testRejectComment() {
		$post_id = $this->factory->post->create( array( 'comment_status' => 'closed' ) );
		$commenter_id = $this->factory->user->create();
		$comment_text = 'TEST COMMENT';

		$command = $this->makeCommandWithMessage();
		$command->set_keys( array( $post_id, $commenter_id, 0 ) );
		$command->set_message( array( 'recipient' => 'unittest@vern.al', 'stripped-text' => $comment_text ) );

		// An email will also be generated - would need dependency injection to prevent that
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$command->execute();
	}

	function testWrongNumberOfKeysException() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$command = $this->makeCommandWithMessage();
		$command->set_keys( array( 3 ) );
		$command->execute();
	}

	function testSanitizeComment() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$commenter_id = $this->factory->user->create();
		$evil_script = '<script>alert("boo!");</script>';
		$comment_text = 'TEST COMMENT' . $evil_script;

		$message = new stdClass();
		$message->message = $comment_text;

		$this->mailer_expects = $this->never();

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $commenter_id, 0 ) );
		$command->set_message( $message );
		$command->execute();

		$comments = get_comments( array(
			'post_id' => $post_id,
			'user_id' => $commenter_id,
		) );

		$this->assertCount( 1, $comments, 'Expected to find new comment.' );
		$this->assertNotContains(
			'<script>',
			$comments[0]->comment_content,
			'Expected script tags in the comment text to be escaped.'
		);

	}

	function testRejectDuplicate() {
		$post_id = $this->factory->post->create();
		$commenter_id = $this->factory->user->create();
		$comment_text = 'TEST COMMENT';

		$this->factory->comment->create(
			array(
				'user_id' => $commenter_id,
				'comment_content' => $comment_text,
				'comment_post_ID' => $post_id,
			)
		);

		$command = $this->makeCommandWithMessage( $comment_text );
		$command->set_keys( array( $post_id, $commenter_id, 0 ) );

		try{
			$command->execute();
		} catch( PHPUnit_Framework_Error_Notice $e ) {

			$check_comments = get_comments( array( 'user_id' => $commenter_id, 'comment_post_ID' => $commenter_id ) );
			$this->assertCount( 1, $check_comments, 'Expected only the original comment.' );

			return;
		}


		$this->fail( 'Expected an exception.' );
	}

}
