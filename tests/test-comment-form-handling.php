<?php

class CommentFormHandlingTest extends Prompt_MockMailerTestCase {

	function setUp() {
		parent::setUp();

		// disable comment notifications
		remove_action(
			'wp_insert_comment',
			array( 'Prompt_Outbound_Handling', 'action_wp_insert_comment' ),
			10
		);
		remove_action(
			'transition_comment_status',
			array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ),
			10
		);
	}

	function tearDown() {
		add_action(
			'wp_insert_comment',
			array( 'Prompt_Outbound_Handling', 'action_wp_insert_comment' ),
			10,
			2
		);
		add_action(
			'transition_comment_status',
			array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ),
			10,
			3
		);
		parent::tearDown();
	}

	function testAssetEnqueueing() {

		$post_id = $this->factory->post->create();

		$content = $this->getCommentFormContent( $post_id );

		$this->assertTrue( wp_script_is( 'prompt-comment-form' ), 'Expected comment form script to be enqueued.' );
		$this->assertTrue( wp_style_is( 'prompt-comment-form' ), 'Expected comment form style to be enqueued.' );
		
	}
	
	function testFormContentLoggedOut() {
		$post_id = $this->factory->post->create();

		$this->assertFalse( is_user_logged_in(), 'Assumed a logged out user.' );

		$content = $this->getCommentFormContent( $post_id );

		$this->assertRegExp(
			'/name="' . Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME . '"/',
			$content,
			'Expected to find checkbox input name.'
		);
	}

	function testFormContentDeliveryOff() {
		Prompt_Core::$options->set( 'enable_comment_delivery', false );

		$post_id = $this->factory->post->create();

		$this->assertFalse( is_user_logged_in(), 'Assumed a logged out user.' );

		$content = $this->getCommentFormContent( $post_id );

		$this->assertNotRegExp(
			'/name="' . Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME . '"/',
			$content,
			'Expected NOT to find checkbox input name.'
		);

		Prompt_Core::$options->reset();
	}

	function testFormContentLoggedIn() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create();

		wp_set_current_user( $user_id );

		$content = $this->getCommentFormContent( $post_id );

		$this->assertRegExp(
			'/name="' . Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME . '"/',
			$content,
			'Expected to find checkbox input name.'
		);

		wp_set_current_user( 0 );
	}

	function testFormContentSubscribed() {
		$post_id = $this->factory->post->create();
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $user_id );

		$content = $this->getCommentFormContent( $post_id );

		$this->assertNotContains(
			'/name="' . Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME . '"/',
			$content,
			'Expected no checkbox.'
		);
	}

	function testFormContentUnsubscribed() {
		$post_id = $this->factory->post->create();
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$content = $this->getCommentFormContent( $post_id );

		$this->assertNotRegExp(
			'/checked="checked"/',
			$content,
			'Expected an unchecked checkbox.'
		);
	}

	function testSubscribeNewUser() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$this->mail_data->comment = array(
			'comment_author' => 'testy',
			'comment_author_email' => 'tester@prompt.vern.al',
			'comment_post_ID' => $post_id,
		);

		$this->mailer_expects = $this->never();

		$comment_id = $this->factory->comment->create_and_get( $this->mail_data->comment );

		$_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;

		$this->mailer_expects = $this->once();
		$this->mailer_will = $this->returnCallback( array( $this, 'verifyNewUserEmail' ) );

		Prompt_Comment_Form_Handling::handle_form( $comment_id, 1 );

		$user = get_user_by( 'email', $this->mail_data->comment['comment_author_email'] );

		$this->assertEmpty( $user, 'Expected commenter to not exist as a user yet.' );
	}

	function testSubscribeExistingUserLoggedOut() {
		$author_id = $this->factory->user->create();
		$subscriber = $this->factory->user->create_and_get();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$this->mail_data->comment = array(
			'comment_author' => $subscriber->display_name,
			'comment_author_email' => $subscriber->user_email,
			'comment_post_ID' => $post_id,
		);

		$this->mailer_expects = $this->never();

		$comment_id = $this->factory->comment->create_and_get( $this->mail_data->comment );

		$_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;

		$this->mailer_expects = $this->once();
		$this->mailer_will = $this->returnCallback( array( $this, 'verifySubscribedEmail' ) );

		Prompt_Comment_Form_Handling::handle_form( $comment_id, 1 );

		$prompt_post = new Prompt_Post( $post_id );

		$this->assertTrue( $prompt_post->is_subscribed( $subscriber->ID ), 'Expected commenter to be subscribed.' );
	}

	function verifyNewUserEmail() {

		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals(
			$this->mail_data->comment['comment_author_email'],
			$values[0]['to_address'],
			'Expected agreement email to be sent to commenter.'
		);

		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( 'agree', $template['html_content'], 'Expected the email to ask for agreement.' );

		return true;
	}

	function testSubscribeNewModeratedUser() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$this->mail_data->comment = array(
			'comment_author' => 'testy',
			'comment_author_email' => 'tester@prompt.vern.al',
			'comment_post_ID' => $post_id,
			'comment_approved' => 0,
		);

		$this->mailer_expects = $this->never();

		$comment = $this->factory->comment->create_and_get( $this->mail_data->comment );

		$_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;

		Prompt_Comment_Form_Handling::handle_form( $comment->comment_ID, $this->mail_data->comment['comment_approved'] );

		$user = get_user_by( 'email', $this->mail_data->comment['comment_author_email'] );
		$this->assertEmpty( $user, 'Expected commenter to not exist as a user yet.' );

		$this->assertNotEmpty(
			get_comment_meta( $comment->comment_ID, Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME, true ),
			'Expected comment subscription request metadata to be added.'
		);
	}

	function testDoNotSubscribeSpamCommenter() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$this->mail_data->comment = array(
			'comment_author' => 'spammy',
			'comment_author_email' => 'spammer@example.com',
			'comment_post_ID' => $post_id,
		);

		$this->mailer_expects = $this->never();

		$comment_id = $this->factory->comment->create_and_get( $this->mail_data->comment );

		$_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;

		Prompt_Comment_Form_Handling::handle_form( $comment_id, 'spam' );

		$user = get_user_by( 'email', $this->mail_data->comment['comment_author_email'] );

		$this->assertEmpty( $user, 'Expected spammer to not exist as a user.' );
	}

	function testDoNotSubscribeInvalidAuthorEmail() {
		$author_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $author_id ) );
		$this->mail_data->comment = array(
			'comment_author' => 'Faker',
			'comment_author_email' => 'foo',
			'comment_post_ID' => $post_id,
		);

		$this->mailer_expects = $this->never();

		$comment_id = $this->factory->comment->create_and_get( $this->mail_data->comment );

		$_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;

		Prompt_Comment_Form_Handling::handle_form( $comment_id, '1' );

		$user = get_user_by( 'email', $this->mail_data->comment['comment_author_email'] );

		$this->assertEmpty( $user, 'Expected invalid email commenter to not exist as a user.' );
	}

	function testSubscribeCurrentUser() {
		$post_id = $this->factory->post->create();

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$this->mail_data->comment = array(
			'user_id' => $user_id,
			'comment_post_ID' => $post_id,
		);

		$comment_id = $this->factory->comment->create_and_get( $this->mail_data->comment );

		$_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;

		$this->mailer_will = $this->returnCallback( array( $this, 'verifySubscribedEmail' ) );

		Prompt_Comment_Form_Handling::handle_form( $comment_id, '1' );

		$prompt_post = new Prompt_Post( $post_id );
		$this->assertTrue( $prompt_post->is_subscribed( $user_id ), 'Expected current user to be subscribed.' );
	}

	function verifySubscribedEmail() {

		if ( isset( $this->mail_data->comment['user_id'] ))
			$user = get_user_by( 'id', $this->mail_data->comment['user_id'] );
		else
			$user = get_user_by( 'email', $this->mail_data->comment['comment_author_email'] );

		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals(
			$user->user_email,
			$values[0]['to_address'],
			'Expected subscribed email to be sent to commenter.'
		);

		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( ' subscribed', $template['subject'], 'Expected to see subscribed in the subject.' );

		return true;
	}

	function testDoNotUnsubscribeCurrentUser() {
		$post_id = $this->factory->post->create();
		$prompt_post = new Prompt_Post( $post_id );

		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );
		$prompt_post->subscribe( $user->ID );

		$comment = array(
			'user_id' => $user->ID,
			'comment_author_email' => $user->user_email,
			'comment_post_ID' => $post_id,
		);

		$comment_id = $this->factory->comment->create_and_get( $comment );

		Prompt_Comment_Form_Handling::handle_form( $comment_id, '1' );

		$this->assertTrue( $prompt_post->is_subscribed( $user->ID ), 'Expected current user to remain subscribed.' );
	}

	function testDoNotUnsubscribeEmailAddress() {
		$post_id = $this->factory->post->create();

		$user = $this->factory->user->create_and_get();

		wp_set_current_user( 0 );

		$comment = array(
			'comment_author' => 'whatever',
			'comment_author_email' => $user->user_email,
			'comment_post_ID' => $post_id,
		);

		$comment_id = $this->factory->comment->create_and_get( $comment );

		$_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;

		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $user->ID );

		Prompt_Comment_Form_Handling::handle_form( $comment_id, '1' );

		$this->assertTrue( $prompt_post->is_subscribed( $user->ID ), 'Expected entered email to remain subscribed.' );
	}

	protected function getCommentFormContent( $post_id ) {
		ob_start();
		Prompt_Comment_Form_Handling::form_content( $post_id );
		return ob_get_clean();
	}

}

