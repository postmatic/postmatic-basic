<?php

class CommentMailerTest extends WP_UnitTestCase {

	protected $user;
	protected $post;
	protected $data;

	public function setUp() {
		parent::setUp();

		// API transport tests both html and text templates
		Prompt_Core::$options->set( 'email_transport', Prompt_Enum_Email_Transports::API );

		$this->post = $this->factory->post->create_and_get();
		$this->user = $this->factory->user->create_and_get();
		$this->data = new stdClass();

		// Disable automatic scheduling
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

	public function tearDown() {
		Prompt_Core::$options->reset();
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
	}

	function getMockFloodController( $comment, $subscriber_ids ) {

		$mock = $this->getMock(
			'Prompt_Comment_Flood_Controller',
			array( 'control_recipient_ids' ),
			array( $comment )
		);
		$mock->expects( $this->once() )
			->method( 'control_recipient_ids' )
			->will( $this->returnValue( $subscriber_ids ) );

		return $mock;
	}

	function testCommentNotification() {
		$this->data->post_subscriber = $this->factory->user->create_and_get();
		$this->data->site_comments_subscriber = $this->factory->user->create_and_get();

		$subscriber_ids = array(
			$this->data->post_subscriber->ID,
			$this->data->site_comments_subscriber->ID,
		);

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'post_outbound_message_batches' )
			->will( $this->returnCallback( array( $this, 'verifyCommentBatch' ) ) );

		$this->data->comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $this->post->ID,
			'comment_content' => 'XXCONTENTXX',
		) );

		$mock_flood_controller = $this->getMockFloodController( $this->data->comment, $subscriber_ids );

		$batch = new Prompt_Comment_Email_Batch( $this->data->comment, $mock_flood_controller );
		$mailer = new Prompt_Comment_Mailer( $batch, $api_mock );

		$mailer->send();
	}

	function verifyCommentBatch( array $data ) {

		$this->assertContains(
			$this->data->comment->comment_content,
			$data['batch_message_template']['html_content'],
			'Expected template HTML to contain comment content.'
		);

		$this->assertContains(
			$this->data->comment->comment_content,
			$data['batch_message_template']['text_content'],
			'Expected template text to contain comment content.'
		);

		$this->assertContains(
			$this->data->comment->comment_author,
			$data['batch_message_template']['html_content'],
			'Expected template HTML to contain comment author.'
		);

		$this->assertContains(
			$this->data->comment->comment_author,
			$data['batch_message_template']['text_content'],
			'Expected template HTML to contain comment author.'
		);

		$this->assertEquals(
			Prompt_Enum_Message_Types::COMMENT,
			$data['batch_message_template']['message_type'],
			'Expected comment message type.'
		);

		$this->assertCount(
			2,
			$data['individual_message_values'],
			'Expected two individual message values.'
		);

		$to_addresses = array(
			$data['individual_message_values'][0]['to_address'],
			$data['individual_message_values'][1]['to_address'],
		);

		$this->assertContains( $this->data->post_subscriber->user_email, $to_addresses );
		$this->assertContains( $this->data->site_comments_subscriber->user_email, $to_addresses );

		$this->assertNotEmpty(
			$data['individual_message_values'][0]['reply_to'],
			'Expected a reply_to value.'
		);
	}
}