<?php

class OutboundHandlingTest extends Prompt_MockMailerTestCase {

	/**
	 * @var array
	 */
	protected $native_notification_addresses;

	function setUp() {
		$this->remove_outbound_hooks = false;
		parent::setUp();
	}

	function testCommentApprove() {

		$post_id = $this->factory->post->create();
		$prompt_post = new Prompt_Post( $post_id );
		$prompt_post->subscribe( $this->factory->user->create() );

		$comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID'  => $post_id,
			'comment_approved' => 0,
			'comment_type'     => 'comment',
		) );

		$comment->comment_approved = 1;

		wp_update_comment( (array) $comment );

		$this->assertInstanceOf( 'Prompt_Comment_Email_Batch', $this->mailer_payload );
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
