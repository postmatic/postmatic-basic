<?php

class CommentFloodControllerTest extends Prompt_MockMailerTestCase {

	function testPostAndSiteSubscribers() {
		$site_subscriber_id = $this->factory->user->create();
		$site_comments = new Prompt_Site_Comments();
		$site_comments->subscribe( $site_subscriber_id );

		$post_subscriber_id = $this->factory->user->create();
		$prompt_post = new Prompt_Post( $this->factory->post->create() );
		$prompt_post->subscribe( $post_subscriber_id );

		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $prompt_post->id() ) );

		$controller = new Prompt_Comment_Flood_Controller( $comment );

		$recipient_ids = $controller->control_recipient_ids();

		$expected_ids = array( $site_subscriber_id, $post_subscriber_id );
		$this->assertEmpty( array_diff( $expected_ids, $recipient_ids ), 'Expected both site and post subscribers.' );
	}

	function testRemoveCommentAuthor() {
		$prompt_post = new Prompt_Post( $this->factory->post->create() );

		$include_subscriber_id = $this->factory->user->create();
		$prompt_post->subscribe( $include_subscriber_id );

		$exclude_subscriber_id = $this->factory->user->create();
		$prompt_post->subscribe( $exclude_subscriber_id );

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $prompt_post->id(), 'user_id' => $exclude_subscriber_id )
		);

		$controller = new Prompt_Comment_Flood_Controller( $comment );

		$recipient_ids = $controller->control_recipient_ids();

		$expected_ids = array( $include_subscriber_id );
		$this->assertEmpty( array_diff( $expected_ids, $recipient_ids ), 'Expected comment author to be removed.' );
	}

	function testIncludePostAuthor() {
		// assume: default auto_subscribe_authors is true
		$author_id = $this->factory->user->create();
		$prompt_post = new Prompt_Post( $this->factory->post->create( array( 'post_author' => $author_id ) ) );

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $prompt_post->id() )
		);

		$controller = new Prompt_Comment_Flood_Controller( $comment );

		$recipient_ids = $controller->control_recipient_ids();

		$expected_ids = array( $author_id );
		$this->assertEmpty( array_diff( $expected_ids, $recipient_ids ), 'Expected post author to be included.' );
	}

	function testExcludePostAuthor() {
		Prompt_Core::$options->set( 'auto_subscribe_authors', false );

		$author_id = $this->factory->user->create();
		$prompt_post = new Prompt_Post( $this->factory->post->create( array( 'post_author' => $author_id ) ) );

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $prompt_post->id() )
		);

		$controller = new Prompt_Comment_Flood_Controller( $comment );

		$recipient_ids = $controller->control_recipient_ids();

		$this->assertEmpty( $recipient_ids, 'Expected no recipients.' );

		Prompt_Core::$options->reset();
	}

	function testExcludePostAuthorOwnComment() {
		// assume: default auto_subscribe_authors is true
		$author_id = $this->factory->user->create();
		$prompt_post = new Prompt_Post( $this->factory->post->create( array( 'post_author' => $author_id ) ) );

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $prompt_post->id(), 'user_id' => $author_id )
		);

		$controller = new Prompt_Comment_Flood_Controller( $comment );

		$recipient_ids = $controller->control_recipient_ids();

		$this->assertEmpty( $recipient_ids, 'Expected no recipients.' );
	}

	function testExcludeSiteCommentSubscriberOwnComment() {

		$subscriber_id = $this->factory->user->create();

		$site_comments = new Prompt_Site_Comments();

		$site_comments->subscribe( $subscriber_id );

		$post = $this->factory->post->create_and_get();

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $post->ID, 'user_id' => $subscriber_id )
		);

		$controller = new Prompt_Comment_Flood_Controller( $comment );

		$recipient_ids = $controller->control_recipient_ids();

		$this->assertCount( 1, $recipient_ids, 'Expected one recipient.' );
		$this->assertContains( $post->post_author, $recipient_ids, 'Expected post author only.' );
	}

	function testFlood() {
		$site_comments = new Prompt_Site_Comments();
		$site_subscriber_id = $this->factory->user->create();
		$site_comments->subscribe( $site_subscriber_id );

		$prompt_post = new Prompt_Post( $this->factory->post->create() );

		$subscriber_ids = $this->factory->user->create_many( 2 );
		$this->mail_data->subscriber_ids = $subscriber_ids;

		$prompt_post->subscribe( $subscriber_ids[0] );
		$prompt_post->subscribe( $subscriber_ids[1] );

		$comments = $this->factory->comment->create_many( 7, array( 'comment_post_ID' => $prompt_post->id() ) );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyFloodNotifications' ) );

		$last_comment = get_comment( $comments[6] );

		$controller = new Prompt_Comment_Flood_Controller( $last_comment );

		$recipient_ids = $controller->control_recipient_ids();

		$this->assertEmpty(
			array_diff( $recipient_ids, array( $site_subscriber_id, $prompt_post->get_wp_post()->post_author ) ),
			'Expected all but site subscriber and post author to be removed.'
		);

		$this->assertEmpty( $prompt_post->subscriber_ids(), 'Expected post subscribers to be unsubscribed.' );

		$next_comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $prompt_post->id() ) );

		$controller = new Prompt_Comment_Flood_Controller( $next_comment );

		$recipient_ids = $controller->control_recipient_ids();

		$this->assertEmpty(
			array_diff( $recipient_ids, array( $site_subscriber_id, $prompt_post->get_wp_post()->post_author ) ),
			'Expected only the site subscriber and post author to receive post-flood comments.'
		);

	}

	function verifyFloodNotifications() {
		$message_values = $this->mailer_payload->get_individual_message_values();
		$this->assertCount( 2, $message_values, 'Expected two notification emails.' );
		$to_addresses = array( $message_values[0]['to_address'], $message_values[1]['to_address'] );
		$subscriber0 = get_userdata( $this->mail_data->subscriber_ids[0] );
		$this->assertContains( $subscriber0->user_email, $to_addresses );
		$subscriber1 = get_userdata( $this->mail_data->subscriber_ids[1] );
		$this->assertContains( $subscriber1->user_email, $to_addresses );
		$metadata = $message_values[0]['reply_to']['trackable-address'];
		$this->assertCount( 3, $metadata->ids, 'Expected comment command metadata.' );

		$message_template = $this->mailer_payload->get_batch_message_template();
		$this->assertEquals(
			Prompt_Enum_Message_Types::SUBSCRIPTION,
			$message_template['message_type'],
			'Expected subscription message type.'
		);
	}

}
