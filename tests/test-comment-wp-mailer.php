<?php

class CommentWpMailerTest extends WP_UnitTestCase {

	protected $user;
	protected $post;
	protected $data;
	/** @var  Prompt_Api_Client */
	protected $api_mock;

	public function setUp() {
		parent::setUp();

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

	function getMockApiClient() {
		$this->api_mock = $this->getMock( 'Prompt_Api_Client' );
		$this->api_mock->expects( $this->once() )
			->method( 'post_outbound_messages' )
			->will( $this->returnCallback( array( $this, 'verifyTrackingRequest' ) ) );
		return $this->api_mock;
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

		$mailer_mock = $this->getMock( 'AdHoc', array( 'send' ) );
		$mailer_mock->expects( $this->exactly( 2 ) )
			->method( 'send' )
			->will( $this->returnCallback( array( $this, 'verifyLocalMailing' ) ) );

		$this->data->comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $this->post->ID,
		) );

		$batch = new Prompt_Comment_Email_Batch(
			$this->data->comment,
			$this->getMockFloodController( $this->data->comment, $subscriber_ids )
		);

		$mailer = new Prompt_Comment_Wp_Mailer( $batch, $this->getMockApiClient(), array( $mailer_mock, 'send' ) );

		$mailer->send();
	}

	function verifyTrackingRequest( $data ) {
		$this->assertGreaterThan( 0, count( $data->outboundMessages ), 'Expected multiple notification emails.' );
		$this->assertEquals(
			Prompt_Enum_Message_Types::COMMENT,
			$data->outboundMessages[0]['type'],
			'Expected comment message type.'
		);

		foreach ( $data->outboundMessages as $index => $message ) {
			$data->outboundMessages[$index]['id'] = $index;
			$data->outboundMessages[$index]['reply_to'] = "reply$index@example.com";
		}
		return array(
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'body' => json_encode( $data )
		);
	}

	function verifyLocalMailing( $to, $subject, $message, $headers ) {

		$expected_to_addresses = array(
			Prompt_Email_Batch::name_address( 
				$this->data->post_subscriber->user_email,
				$this->data->post_subscriber->display_name
			),
			Prompt_Email_Batch::name_address(
				$this->data->site_comments_subscriber->user_email,
				$this->data->site_comments_subscriber->display_name
			)
		);
		$this->assertContains( $to, $expected_to_addresses );
		$this->assertContains( $this->data->comment->comment_author, $message );
		
		$reply_to_filter = create_function( '$a', 'return (strpos( $a, "Reply-To:" ) === 0);' );
		$this->assertNotEmpty( array_filter( $headers, $reply_to_filter ), 'Expected a reply-to header.' );
		
		$this->assertNotContains( 'Notice:', $message, 'Expected no notices.' );
		$this->assertNotContains( 'Error:', $message, 'Expected no errors.' );

		$this->assertContains(
			$this->data->comment->comment_content,
			$message,
			'Expected comment content in HTML.'
		);

		$this->assertNotContains(
			'{{subscriber_comment_intro}}',
			$message,
			'Expected subscriber intro handlebars in HTML to be replaced.'
		);

	}

	function testAnonymousCommentNotification() {
		$this->data->subscriber = $this->factory->user->create_and_get();
		$post_id = $this->factory->post->create();

		$this->data->comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
			'comment_author' => '',
			'comment_author_email' => '',
		) );

		$batch = new Prompt_Comment_Email_Batch(
			$this->data->comment,
			$this->getMockFloodController( $this->data->comment, array( $this->data->subscriber->ID ) )
		);

		$mailer_mock = $this->getMock( 'PHPMailer', array( 'send' ) );
		$mailer_mock->expects( $this->once() )
			->method( 'send' )
			->will( $this->returnCallback( array( $this, 'verifyAnonymousLocalMailing' ) ) );

		$mailer = new Prompt_Comment_Wp_Mailer( $batch, $this->getMockApiClient(), array( $mailer_mock, 'send' ) );

		$mailer->send();
	}

	function verifyAnonymousLocalMailing( $to, $subject, $message, $headers ) {
		$this->assertContains( $this->data->subscriber->user_email, $to );
		$this->assertContains( __( 'Anonymous' ), $message );
	}

	function testCommentReplyNotification() {
		$this->data->parent_author = $this->factory->user->create_and_get();
		$child_author = $this->factory->user->create_and_get();
		$post_id = $this->factory->post->create();

		$parent_comment = array(
			'user_id' => $this->data->parent_author->ID,
			'comment_post_ID' => $post_id,
			'comment_content' => 'test comment',
			'comment_agent' => 'Prompt',
			'comment_author' => $this->data->parent_author->display_name,
			'comment_author_IP' => '',
			'comment_author_url' => $this->data->parent_author->user_url,
			'comment_author_email' => $this->data->parent_author->user_email,
		);

		$parent_comment_id = wp_insert_comment( $parent_comment );

		$child_comment = array(
			'user_id' => $child_author->ID,
			'comment_post_ID' => $post_id,
			'comment_parent' => $parent_comment_id,
			'comment_content' => 'test reply',
			'comment_agent' => 'Prompt',
			'comment_author' => $child_author->display_name,
			'comment_author_IP' => '',
			'comment_author_url' => $child_author->user_url,
			'comment_author_email' => $child_author->user_email,
		);

		$this->data->child_comment_id = wp_insert_comment( $child_comment );

		$child_comment = get_comment( $this->data->child_comment_id );

		$batch = new Prompt_Comment_Email_Batch(
			$child_comment,
			$this->getMockFloodController( $child_comment, array( $this->data->parent_author->ID ) )
		);

		$mailer_mock = $this->getMock( 'AdHoc', array( 'send' ) );
		$mailer_mock->expects( $this->once() )
			->method( 'send' )
			->will( $this->returnCallback( array( $this, 'verifyCommentReplyLocalMailing' ) ) );

		$mailer = new Prompt_Comment_Wp_Mailer( $batch, $this->getMockApiClient(), array( $mailer_mock, 'send' ) ); 
		
		$mailer->send();
	}

	function verifyCommentReplyLocalMailing( $to, $subject, $message, $headers ) {

		$this->assertContains(
			$this->data->parent_author->user_email,
			$to,
			'Expected an email to be sent to the parent comment author.'
		);
		
		$this->assertContains(
			'Reply-To: reply0@example.com',
			$headers,
			'Expected a trackable reply address.'
		);
	}

	function testChunking() {

		$chunk_size = 3;
		Prompt_Core::$options->set( 'emails_per_chunk', $chunk_size );

		$subscriber_ids = $this->factory->user->create_many( $chunk_size + 1 );

		$post_id = $this->factory->post->create();

		$this->data->comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
		) );

		$mock_flood_controller = $this->getMockFloodController(
			$this->data->comment,
			$subscriber_ids
		);

		$batch = new Prompt_Comment_Email_Batch( $this->data->comment, $mock_flood_controller );

		$mailer_mock = $this->getMock( 'AdHoc', array( 'send' ) );
		$mailer_mock->expects( $this->any() )->method( 'send' )->will( $this->returnValue( true ) );

		$client_mock = $this->getMockApiClient();
		$client_mock->expects( $this->once() )
			->method( 'post_instant_callback' )
			->will( $this->returnCallback( array( $this, 'verifyChunkMetadata' ) ) );

		$mailer = new Prompt_Comment_Wp_Mailer( $batch, $client_mock, array( $mailer_mock, 'send' ) );

		$this->data->verified_chunk_event = false;
		$mailer->send();

		$this->assertTrue( $this->data->verified_chunk_event, 'Expected another mailing to be scheduled.' );

		Prompt_Core::$options->reset();
	}

	function verifyChunkMetadata( $data ) {

		$this->assertArrayHasKey( 'metadata', $data );

		$this->assertEquals( 'prompt/comment_mailing/send_notifications', $data['metadata'][0] );

		$this->assertEquals( $this->data->comment->comment_ID, $data['metadata'][1][0] );

		$this->data->verified_chunk_event = true;

		return true;
	}


	function testClearFailures() {

		$recipient = $this->factory->user->create_and_get();

		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $this->factory->post->create() ) );

		$mock_flood_controller = $this->getMockFloodController( $comment, array( $recipient->ID ) );

		$batch_mock = $this->getMock(
			'Prompt_Comment_Email_Batch',
			array( 'get_individual_message_values', 'clear_failures' ),
			array( $comment, $mock_flood_controller )
		);

		$individual_values = array( array( 'id' => $recipient->ID, 'to_address' => $recipient->user_email ) );

		$batch_mock->expects( $this->any() )
			->method( 'get_individual_message_values' )
			->willReturn( $individual_values );

		$batch_mock->expects( $this->once() )
			->method( 'clear_failures' )
			->with( array( $recipient->user_email ) );

		$api_mock = $this->getMock( 'Prompt_Api_Client' );

		$mailer = new Prompt_Comment_Wp_Mailer( $batch_mock, $api_mock, '__return_false' );
		$mailer->send();
	}
}