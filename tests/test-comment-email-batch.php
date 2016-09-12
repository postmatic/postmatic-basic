<?php

class CommentEmailTest extends Prompt_UnitTestCase {

	function testDefault() {
		$post_id = $this->factory->post->create();
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $post_id ) );

		$batch = new Prompt_Comment_Email_Batch( $comment );

		$this->assertEmpty( $batch->get_individual_message_values(), 'Expected no recipient values.' );

		$template = $batch->get_batch_message_template();
		$this->assertContains(
			$comment->comment_author,
			$template['from_name'],
			'Expected comment author in from name.'
		);
		$this->assertEquals(
			Prompt_Enum_Message_Types::COMMENT,
			$template['message_type'],
			'Expected comment message type.'
		);
	}

	function testAnonymous() {
		$post_id = $this->factory->post->create();
		$comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
			'comment_author' => '',
		) );

		$batch = new Prompt_Comment_Email_Batch( $comment );

		$template = $batch->get_batch_message_template();
		$this->assertContains(
			'Anonymous',
			$template['from_name'],
			'Expected Anonymous in from name.'
		);
	}

	function testDefaultRecipient() {
		$post_id = $this->factory->post->create();
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $post_id ) );
		$recipient = $this->factory->user->create_and_get();
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $recipient->ID );

		$batch = new Prompt_Comment_Email_Batch( $comment );

		$values = $batch->get_individual_message_values();
		$this->assertCount( 1, $values );

		$values = $values[0];
		$this->assertEquals( $recipient->user_email, $values['to_address'], 'Expected recipient to address.' );
		$this->assertEquals( $recipient->display_name, $values['to_name'], 'Expected recipient to name.' );
		$this->assertNotEmpty( $values['reply_to'], 'Expected command metadata.' );
		$this->assertArrayHasKey( 'unsubscribe_url', $values );
		$this->assertContains( $comment->comment_author, $values['subject'], 'Expected subject to contain author name.' );
		$this->assertContains(
			$prompt_post->get_wp_post()->post_title,
			$values['subject'],
			'Expected subject to contain post title.'
		);
	}
	
	function testPersonalizedSubject() {
		$post_id = $this->factory->post->create();
		$parent_author = $this->factory->user->create_and_get();
		$parent_comment = $this->factory->comment->create_and_get( array( 
			'comment_post_ID' => $post_id,
			'comment_author_email' => $parent_author->user_email,
		) );
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $parent_author->ID );
		
		$child_comment = $this->factory->comment->create_and_get( array( 
			'comment_post_ID' => $post_id,
			'comment_parent' => $parent_comment->comment_ID,
		) );

		$batch = new Prompt_Comment_Email_Batch( $child_comment );
		
		$values = $batch->get_individual_message_values();
		$this->assertCount( 1, $values );

		$values = $values[0];
		$this->assertContains( $child_comment->comment_author, $values['subject'] );
		$this->assertContains( 'replied to your comment', $values['subject'] );
	}

	function testReplyUnrenderedContent() {
		Prompt_Core::$options->set( 'email_transport', Prompt_Enum_Email_Transports::API );
		$post_id = $this->factory->post->create();
		$parent_comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
		) );
		$comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
			'comment_parent' => $parent_comment->comment_ID,
			'comment_content' => 'XXCONTENTXX',
		) );
		$recipient = $this->factory->user->create_and_get();
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $recipient->ID );

		$batch = new Prompt_Comment_Email_Batch( $comment );

		$template = $batch->get_batch_message_template();

		$this->assertNotContains(
			'Error:',
			$template['html_content'],
			'Expected no error notifications.'
		);

		$this->assertNotContains(
			'Warning:',
			$template['html_content'],
			'Expected no error notifications.'
		);

		$this->assertNotContains(
			'Error:',
			$template['text_content'],
			'Expected no error notifications.'
		);

		$this->assertNotContains(
			'Warning:',
			$template['text_content'],
			'Expected no error notifications.'
		);

		$this->assertContains(
			$comment->comment_content,
			$template['html_content'],
			'Expected comment content in HTML.'
		);

		$this->assertContains(
			$comment->comment_content,
			$template['text_content'],
			'Expected comment content in text.'
		);

		$this->assertContains(
			'{{subscriber_comment_intro_html}}',
			$template['html_content'],
			'Expected subscriber intro handlebars in HTML.'
		);

		$this->assertContains(
			'{{subscriber_comment_intro_text}}',
			$template['text_content'],
			'Expected subscriber intro handlebars in text.'
		);

		$this->assertContains(
			sprintf( '{{{reply_to_comment_%d}}}', $parent_comment->comment_ID ),
			$template['html_content'],
			'Expected reply link macro for parent comment.'
		);

		$this->assertContains(
			sprintf( '{{{reply_to_comment_%d}}}', $parent_comment->comment_ID ),
			$template['text_content'],
			'Expected reply link macro for parent comment.'
		);

		$values = $batch->get_individual_message_values();

		$this->assertCount( 1, $values, 'Expected one recipient.' );
		$this->assertArrayHasKey( 'reply_to_comment_' . $parent_comment->comment_ID, $values[0] );

		Prompt_Core::$options->reset();
	}

	function testLock() {
		$post_id = $this->factory->post->create();
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $post_id ) );
		$recipient = $this->factory->user->create_and_get();
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $recipient->ID );

		$batch1 = new Prompt_Comment_Email_Batch( $comment );
		$batch1->lock_for_sending();

		$batch2 = new Prompt_Comment_Email_Batch( $comment );

		$this->assertEmpty( $batch2->get_individual_message_values(), 'Expected no recipients in post-lock batch.' );

		$batch1->clear_for_retry();
		$batch3 = new Prompt_Comment_Email_Batch( $comment );
		$this->assertCount( 1, $batch3->get_individual_message_values(), 'Expected one recipient post-cleared batch.' );

		$batch1->lock_for_sending();
		$batch4 = new Prompt_Comment_Email_Batch( $comment );
		$this->assertEmpty( $batch4->get_individual_message_values(), 'Expected no recipients in post-lock batch.' );

		$batch1->clear_failures( array( $recipient->user_email ) );
		$batch5 = new Prompt_Comment_Email_Batch( $comment );
		$this->assertCount( 1, $batch5->get_individual_message_values(), 'Expected one recipient post-failure-cleared batch.' );
	}

}
