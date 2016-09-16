<?php

class PostEmailTest extends WP_UnitTestCase {

	function testDefaults() {

		$post = $this->factory->post->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );

		$template = $batch->get_batch_message_template();

		$this->assertEquals( '{{{to_address}}}', $template['to_address'], 'Expected the default to address template.' );
		$this->assertEquals( '{{{to_name}}}', $template['to_name'], 'Expected the default to address template.' );

		$this->assertContains( $post->post_title, $template['subject'], 'Expected post title in subject.' );
		$this->assertContains(
			'{{subscribed_object_label}}',
			$template['footnote_html'],
			'Expected subscription title in footnote.'
		);
		$this->assertContains(
			'{{subscribed_object_label}}',
			$template['footnote_text'],
			'Expected subscription title in footnote.'
		);
		$this->assertContains(
			'body=unsubscribe',
			$template['footnote_html'],
			'Expected unsubscribe body URL parameter in footnote.'
		);

		$this->assertEquals(
			Prompt_Enum_Message_Types::POST,
			$template['message_type'],
			'Expected post message type.'
		);

		$default_values = $batch->get_default_values();
		$this->assertEquals(
			get_option( 'blogname' ),
			$default_values['from_name'],
			'Expected blogname in default from name. '
		);

		$this->assertEmpty( $batch->get_individual_message_values(), 'Expected no individual values.' );
	}

	function testDefaultRecipient() {

		$post = $this->factory->post->create_and_get();
		$recipient = $this->factory->user->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );
		$batch->add_recipient( new Prompt_User( $recipient ) );

		$values = $batch->get_individual_message_values();
		$this->assertEquals( $recipient->user_email, $values[0]['to_address'], 'Expected recipient to address.' );
		$this->assertEquals( $recipient->display_name, $values[0]['to_name'], 'Expected recipient to name.' );
		$this->assertNotEmpty( $values[0]['reply_to'], 'Expected reply to macro.' );
	}

	/**
	 * @expectedException PHPUnit_Framework_Error
	 * @expectedExceptionMessage Did not add an invalid post recipient
	 */
	function testNonexistentRecipient() {

		$post = $this->factory->post->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );

		$batch->add_recipient( new Prompt_User( -1 ) );

		$this->assertEmpty( $batch->get_individual_message_values(), 'Expected no individual message values.' );
	}

	function testAuthorSubscriberFromName() {

		$author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );
		$post = $this->factory->post->create_and_get( array( 'post_author' => $author->ID ) );
		$recipient = $this->factory->user->create_and_get();

		$prompt_author = new Prompt_User( $author );
		$prompt_author->subscribe( $recipient->ID );

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );
		$batch->add_recipient( new Prompt_User( $recipient ) );

		$values = $batch->get_individual_message_values();
		$this->assertContains(
			get_option( 'blogname' ),
			$values[0]['from_name'],
			'Expected blogname in from name.'
		);

		$this->assertContains(
			$author->display_name,
			$values[0]['from_name'],
			'Expected author name in from name.'
		);
	}

	function testRecipientValues() {
		$post = $this->factory->post->create_and_get();
		$recipient = $this->factory->user->create_and_get();
		$prompt_site = new Prompt_Site();

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );
		$batch->add_recipient( new Prompt_User( $recipient ) );

		$values = $batch->get_individual_message_values();

		$this->assertEquals( $recipient->display_name, $values[0]['to_name'] );
		$this->assertEquals( $recipient->user_email, $values[0]['to_address'] );
		$this->assertArrayNotHasKey( 'from_name', $values[0] );
		$this->assertArrayHasKey( 'unsubscribe_url', $values[0] );
		$this->assertEquals( $prompt_site->subscription_object_label(), $values[0]['subscribed_object_label'] );
	}

	function testClosedPost() {
		$author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );

		$post = $this->factory->post->create_and_get( array(
			'post_status' => 'draft',
			'post_content' => 'XXCONTENTXX',
			'post_excerpt' => 'XXEXCERPTXX',
			'post_author' => $author->ID,
			'comment_status' => 'closed',
		) );

		$recipient = $this->factory->user->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );
		$batch->add_recipient( new Prompt_User( $recipient ) );

		$template = $batch->get_batch_message_template();

		$this->assertEquals(
			Prompt_Email_Batch::default_from_email(),
			$template['from_address'],
			'Expected the default from address.'
		);

		$this->assertContains( $post->post_content, $template['html_content'], 'Expected post content in HTML.' );

		$this->assertNotContains( $post->post_excerpt, $template['html_content'], 'Expected NO post excerpt in HTML.' );

		$values = $batch->get_individual_message_values();

		$this->assertArrayHasKey( 'reply_to', $values[0], 'Expected the email to have command metadata.' );

		$this->assertArrayHasKey( 'trackable-address', $values[0]['reply_to'], 'Expected a trackable address macro.' );

		$site = new Prompt_Site();
		$forward_command = new Prompt_Forward_Command();
		$forward_command->set_subscription_object( $site )
			->set_from_user_id( $recipient->ID )
			->set_to_user_id( $author->ID );

		$meta = Prompt_Command_Handling::get_command_metadata( $forward_command );

		$this->assertEquals(
			$meta,
			$values[0]['reply_to']['trackable-address'],
			'Expected post forward command metadata.'
		);

		$this->assertArrayNotHasKey( 'reply_address', $values[0], 'Expected no reply address' );
	}

	function testExcerptPostEmail() {
		$author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );

		$post = $this->factory->post->create_and_get( array(
			'post_status' => 'draft',
			'post_content' => 'XXCONTENTXX',
			'post_excerpt' => 'XXEXCERPTXX',
			'post_author' => $author->ID,
			'comment_status' => 'closed',
		) );

		$recipient = $this->factory->user->create_and_get();

		$_POST['post_ID'] = $post->ID;
		$_POST['prompt_excerpt_only'] = 1;

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );
		$batch->add_recipient( new Prompt_User( $recipient ) );

		$template = $batch->get_batch_message_template();

		$this->assertNotContains( $post->post_content, $template['html_content'], 'Expected NO post content in HTML.' );

		$this->assertContains( $post->post_excerpt, $template['html_content'], 'Expected post excerpt in HTML.' );

		$values = $batch->get_individual_message_values();

		$this->assertArrayHasKey( 'reply_to', $values[0], 'Expected the email to have command metadata.' );

		$this->assertArrayNotHasKey( 'reply_address', $values[0], 'Expected no reply address' );
	}


	function testOverrideExcerptPostEmail() {
		$author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );

		$post = $this->factory->post->create_and_get( array(
			'post_status' => 'draft',
			'post_content' => 'XXCONTENTXX',
			'post_excerpt' => 'XXEXCERPTXX',
			'post_author' => $author->ID,
			'comment_status' => 'closed',
		) );

		add_post_meta( $post->ID, 'prompt_excerpt_only', 1 );

		$recipient = $this->factory->user->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context, array( 'excerpt_only' => false ) );

		$batch->add_recipient( new Prompt_User( $recipient ) );

		$template = $batch->get_batch_message_template();

		$this->assertContains( $post->post_content, $template['html_content'], 'Expected post content in HTML.' );

		$this->assertNotContains( $post->post_excerpt, $template['html_content'], 'Expected NO post excerpt in HTML.' );

		$values = $batch->get_individual_message_values();

		$this->assertArrayHasKey( 'reply_to', $values[0], 'Expected the email to have command metadata.' );
	}

	function testUnrenderedContent() {
		$author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );

		$post = $this->factory->post->create_and_get( array(
			'post_content' => 'XXCONTENTXX',
			'post_author' => $author->ID,
		) );

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );

		$template = $batch->get_batch_message_template();

		$this->assertContains( $post->post_content, $template['html_content'], 'Expected post content in HTML.' );

	}

	function testFootnoteFilter() {

		$mock_filter = $this->getMock( 'Foo', array( 'filter' ) );
		$mock_filter->expects( $this->once() )
			->method( 'filter' )
			->willReturn( array( 'TESTHTML', 'TESTTEXT' ) );

		add_filter( 'prompt/post_email_batch/extra_footnote_content', array( $mock_filter, 'filter' ) );

		$author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );

		$post = $this->factory->post->create_and_get( array( 'post_author' => $author->ID, ) );

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );

		$batch_template = $batch->get_batch_message_template();

		$this->assertContains( 'TESTHTML', $batch_template['footnote_html'] );
		$this->assertContains( 'TESTTEXT', $batch_template['footnote_text'] );

		remove_filter( 'prompt/post_email_batch/extra_footnote_content', array( $mock_filter, 'filter' ) );
	}

	function testComments() {
		remove_action( 'transition_comment_status', array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ) );
		remove_action( 'wp_insert_comment', array( 'Prompt_Outbound_Handling', 'action_wp_insert_comment' ) );

		$post = $this->factory->post->create_and_get();
		$recipient = $this->factory->user->create_and_get();
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $post->ID ) );

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );
		$batch->add_recipient( new Prompt_User( $recipient ) );

		$template = $batch->get_batch_message_template();

		$this->assertContains( 'previous-comments', $template['html_content'], 'Expected previous comments class.' );
		$this->assertContains( $comment->comment_content, $template['html_content'], 'Expected comment content.' );
		$this->assertContains(
			sprintf( '{{{reply_to_comment_%d}}}', $comment->comment_ID ),
			$template['html_content'],
			'Expected reply to comment macro in content.'
		);

		$values = $batch->get_individual_message_values();

		$this->assertCount( 1, $values, 'Expected one recipient.' );
		$this->assertArrayHasKey( 'reply_to_comment_' . $comment->comment_ID, $values[0] );

		add_action( 'transition_comment_status', array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ), 10, 3 );
		add_action( 'wp_insert_comment', array( 'Prompt_Outbound_Handling', 'action_wp_insert_comment' ), 10, 2 );
	}

	function testIncludeAuthorFilter() {
		add_filter( 'prompt/new_post_email/include_author', '__return_true' );

		$author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );

		$post = $this->factory->post->create_and_get( array(
			'post_content' => 'XXCONTENTXX',
			'post_author' => $author->ID,
		) );

		$context = new Prompt_Post_Rendering_Context( $post->ID );

		$batch = new Prompt_Post_Email_Batch( $context );

		$template = $batch->get_batch_message_template();

		$this->assertContains( $author->display_name, $template['html_content'], 'Expected author name in HTML.' );

		remove_filter( 'prompt/new_post_email/include_author', '__return_true' );
	}

	function testLockForSending() {
		$post = $this->factory->post->create_and_get();

		$recipient = $this->factory->user->create_and_get();

		$post_mock = $this->getMock( 'Prompt_Post', array( 'add_sent_recipient_ids' ), array( $post ) );
		$post_mock->expects( $this->once() )
			->method( 'add_sent_recipient_ids' )
			->with( array( $recipient->ID ) );

		$context_mock = $this->getMock( 'Prompt_Post_Rendering_Context', array( 'get_post' ), array( $post->ID ) );
		$context_mock->expects( $this->any() )
			->method( 'get_post' )
			->willReturn( $post_mock );

		$batch = new Prompt_Post_Email_Batch( $context_mock );

		$batch->set_individual_message_values( array(
			array( 'id' => $recipient->ID ),
		) );

		$batch->lock_for_sending();
	}

	function testClearFailures() {
		$post = $this->factory->post->create_and_get();

		$failed_recipient = $this->factory->user->create_and_get();
		$ok_recipient = $this->factory->user->create_and_get();

		$post_mock = $this->getMock( 'Prompt_Post', array( 'remove_sent_recipient_ids' ), array( $post ) );
		$post_mock->expects( $this->once() )
			->method( 'remove_sent_recipient_ids' )
			->with( array( $failed_recipient->ID ) );

		$context_mock = $this->getMock( 'Prompt_Post_Rendering_Context', array( 'get_post' ), array( $post->ID ) );
		$context_mock->expects( $this->any() )
			->method( 'get_post' )
			->willReturn( $post_mock );

		$batch = new Prompt_Post_Email_Batch( $context_mock );

		$batch->set_individual_message_values( array(
			array( 'id' => $failed_recipient->ID, 'to_address' => $failed_recipient->user_email ),
			array( 'id' => $ok_recipient->ID, 'to_address' => $ok_recipient->user_email ),
		) );

		$batch->clear_failures( array( $failed_recipient->user_email ) );
	}

	function testClearForRetry() {
		$post = $this->factory->post->create_and_get();

		$recipient = $this->factory->user->create_and_get();

		$post_mock = $this->getMock( 'Prompt_Post', array( 'remove_sent_recipient_ids' ), array( $post ) );
		$post_mock->expects( $this->once() )
			->method( 'remove_sent_recipient_ids' )
			->with( array( $recipient->ID ) );

		$context_mock = $this->getMock( 'Prompt_Post_Rendering_Context', array( 'get_post' ), array( $post->ID ) );
		$context_mock->expects( $this->any() )
			->method( 'get_post' )
			->willReturn( $post_mock );

		$batch = new Prompt_Post_Email_Batch( $context_mock );

		$batch->set_individual_message_values( array(
			array( 'id' => $recipient->ID ),
		) );

		$batch->clear_for_retry();
	}

}
