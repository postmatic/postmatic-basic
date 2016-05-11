<?php

class OutboundHandlingTest extends Prompt_MockMailerTestCase {

	function setUp() {
		$this->remove_outbound_hooks = false;
		parent::setUp();
	}

	function testPostPublish() {

		$post = $this->factory->post->create_and_get( array( 'post_status' => 'draft' ) );
		$subscriber_id = $this->factory->user->create();

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$post->post_status = 'publish';
		wp_update_post( $post );

		$this->assertInstanceOf( 'Prompt_Post_Email_Batch', $this->mailer_payload );
	}

	function testPostPublishDeliveryOff() {
		Prompt_Core::$options->set( 'enable_post_delivery', false );

		$post = $this->factory->post->create_and_get( array( 'post_status' => 'draft' ) );
		$subscriber_id = $this->factory->user->create();

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$this->mailer_expects = $this->never();

		$post->post_status = 'publish';
		wp_update_post( $post );

		Prompt_Core::$options->reset();
	}

	function testCommentPublish() {

		$post_id = $this->factory->post->create();
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $this->factory->user->create() );

		$comment_id = $this->factory->comment->create( array(
			'comment_post_ID' => $post_id,
		) );

		$this->assertInstanceOf( 'Prompt_Comment_Email_Batch', $this->mailer_payload );
	}

	function testCommentPublishDeliveryOff() {
		Prompt_Core::$options->set( 'enable_comment_delivery', false );

		$post_id = $this->factory->post->create();
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $this->factory->user->create() );

		$this->mailer_expects = $this->never();

		$this->factory->comment->create( array(
			'comment_post_ID' => $post_id,
		) );

		Prompt_Core::$options->reset();
	}

	function testCommentApprove() {

		$post_id = $this->factory->post->create();
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $this->factory->user->create() );

		$comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
			'comment_approved' => 0,
		) );

		$comment->comment_approved = 1;

		wp_update_comment( (array) $comment );

		$this->assertInstanceOf( 'Prompt_Comment_Email_Batch', $this->mailer_payload );
	}

	function testPostNotificationMetaboxOverride() {

		$post = $this->factory->post->create_and_get( array( 'post_status' => 'draft' ) );
		$subscriber_id = $this->factory->user->create();

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$_POST['post_ID'] = $post->ID;
		$_POST['prompt_no_email'] = true;

		$post->post_status = 'publish';
		wp_update_post( $post );

		$this->assertNull( $this->mailer_payload, 'Expected no mailer to be created.' );
	}

	/**
	 * http://docs.gopostmatic.com/article/148-will-trashed-posts-be-mailed-when-i-restore-them
	 */
	function testPostNotificationTrashOverride() {

		$post = $this->factory->post->create_and_get( array( 'post_status' => 'trash' ) );
		$subscriber_id = $this->factory->user->create();

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$_POST['post_ID'] = $post->ID;

		$post->post_status = 'publish';
		wp_update_post( $post );

		$this->assertNull( $this->mailer_payload, 'Expected no mailer to be created.' );
	}

	function testNativeCommentNotification() {
		remove_action( 'transition_comment_status', array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ) );
		add_filter( 'comment_notification_recipients', array( $this, 'checkNativeNotification' ), 20 );
		Prompt_Core::$options->set( 'auto_subscribe_authors', false );

		$author = $this->factory->user->create_and_get();
		$post_id = $this->factory->post->create( array( 'post_author' => $author->ID ) );
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $post_id ) );

		wp_notify_postauthor( $comment_id );

		$this->assertContains(
			$author->user_email,
			$this->native_notification_addresses,
			'Expected an author comment notification.'
		);

		Prompt_Core::$options->reset();
		remove_filter( 'comment_notification_recipients', array( $this, 'checkNativeNotification' ), 20 );
		add_action( 'transition_comment_status', array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ), 10, 3 );
	}

	function checkNativeNotification( $addresses ) {
		$this->native_notification_addresses = $addresses;
		// Prevent actual sending
		return array();
	}
}

