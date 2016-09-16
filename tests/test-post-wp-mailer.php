<?php
/** @group debug */
class PostWpMailerTest extends WP_UnitTestCase {

	protected $data;

	function setUp() {
		parent::setUp();
		$this->data = new stdClass();
	}

	function testPostNotifications() {
		add_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
		add_shortcode( 'unsupportedsc', array( $this, 'unsupportedsc' ) );
		add_shortcode( 'wpgist', array( $this, 'unsupportedsc' ) );
		$this->data->author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );
		wp_set_current_user( $this->data->author->ID );

		$this->data->author_subscriber = $this->factory->user->create_and_get();
		$site_subscriber = $this->factory->user->create_and_get();
		$post_subscriber = $this->factory->user->create_and_get();
		$this->data->title = 'Test & Title';
		$this->data->img_src = 'http://test.tld/image.png';
		$this->data->height_attribute = 'height="200"';
		$this->data->noscript_content = 'scriptless content';
		$post = $this->factory->post->create_and_get( array(
			'post_title' => $this->data->title,
			'post_status' => 'draft',
			'post_author' => $this->data->author->ID,
			'post_content' => 'test content <!--more--> after more <img src="' .
				$this->data->img_src . '" class="test" width="1700" ' .
				$this->data->height_attribute .
				"/> and [unsupportedsc width=\"100\"]inner[/unsupportedsc] shortcode and \n<noscript>" .
				$this->data->noscript_content . "</noscript>\n",
		) );

		$prompt_site = new Prompt_Site;
		$prompt_post = new Prompt_Post( $post->ID );
		$prompt_author = new Prompt_User( $this->data->author->ID );

		$prompt_author->subscribe( $this->data->author_subscriber->ID );
		$prompt_site->subscribe( $site_subscriber->ID );
		$prompt_post->subscribe( $post_subscriber->ID );

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'post_outbound_messages' )
			->will( $this->returnCallback( array( $this, 'verifyTrackingRequest' ) ) );

		$mailer_mock = $this->getMock( 'AdHoc', array( 'send' ) );
		$mailer_mock->expects( $this->exactly( 2 ) )
			->method( 'send' )
			->will( $this->returnCallback( array( $this, 'verifyLocalMailing' ) ) );

		$batch = new Prompt_Post_Email_Batch( new Prompt_Post_Rendering_Context( $post ) );

		$post_mailer = new Prompt_Post_Wp_Mailer( $batch, $api_mock, array( $mailer_mock, 'send' ) );

		$post_mailer->send();

		remove_shortcode( 'unsupportedsc' );
		wp_set_current_user( 0 );
	}

	function unsupportedsc() {
		return 'I\'m unsupported!';
	}

	function verifyTrackingRequest( $batch ) {
		$this->assertGreaterThan( 1, count( $batch->outboundMessages ), 'Expected multiple notification emails.' );
		foreach ( $batch->outboundMessages as $index => $message ) {
			$batch->outboundMessages[$index]['id'] = $index;
			$batch->outboundMessages[$index]['reply_to'] = "reply$index@example.com";
		}
		return array(
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'body' => json_encode( $batch )
		);
	}

	function verifyLocalMailing( $to, $subject, $message, $headers ) {

		$this->assertNotContains( 'Error:', $message, 'Expected no error notifications.' );
		$this->assertNotContains( 'Warning:', $message, 'Expected no warning notifications.' );

		$this->assertContains( $this->data->title, $subject );
		$this->assertContains( '<p>', $message, 'Expected auto paragraph tag in content.' );
		$this->assertNotContains( '[unsupportedsc', $message, 'Expected shortcode to be stripped.' );
		$this->assertNotContains( '<iframe', $message, 'Expected iframe to be stripped.' );

		$this->assertContains(
			$this->data->img_src,
			$message,
			'Expected image to be retained.'
		);
		$this->assertNotContains(
			'incompatible',
			$message,
			'Expected no incompatible messages to be included.'
		);
		$this->assertNotContains(
			'www-youtube-com',
			$message,
			'Expected no youtube incompatible class.'
		);
		$this->assertNotContains( '<noscript>', $message, 'Expected noscript tags to be stripped.' );
		$this->assertContains(
			$this->data->noscript_content,
			$message,
			'Expected noscript content.'
		);
		$this->assertNotContains(
			'{{subscribed_object_label}}',
			$message,
			'Expected NO handlebars subscription object label.'
		);

		$this->assertContains(
			htmlentities( $this->data->title ),
			$message,
			'Expected site subscription object label.'
		);

		$from_name = get_option( 'blogname' );

		if ( strpos( $to, $this->data->author_subscriber->user_email ) !== false ) {
			$from_name .= ' [' . $this->data->author->display_name . ']';
		}

		$from_filter = create_function( '$a', 'return (strpos( $a, "From: ' . $from_name . '") === 0);' );
		$this->assertNotEmpty(
			array_filter( $headers, $from_filter ),
			'Expected from name to be adjusted to subscribed object.'
		);

		$reply_to_filter = create_function( '$a', 'return (strpos( $a, "Reply-To:" ) === 0);' );
		$this->assertNotEmpty( array_filter( $headers, $reply_to_filter ), 'Expected a reply-to header.' );
	}

	function testChunking() {

		$chunk_size = 3;
		Prompt_Core::$options->set( 'emails_per_chunk', $chunk_size );

		$subscriber_ids = $this->factory->user->create_many( $chunk_size + 1 );

		$this->data->prompt_post = new Prompt_Post( $this->factory->post->create() );

		$site = new Prompt_Site();
		foreach ( $subscriber_ids as $id ) {
			$site->subscribe( $id );
		}

		$api_mock = $this->getMock( 'Prompt_Api_Client' );

		$api_mock->expects( $this->once() )
			->method( 'post_outbound_messages' )
			->will( $this->returnCallback( array( $this, 'verifyTrackingRequest' ) ) );

		$api_mock->expects( $this->once() )
			->method( 'post_instant_callback' )
			->will( $this->returnCallback( array( $this, 'verifyChunkMetadata' ) ) );

		$mailer_mock = $this->getMock( 'AdHoc', array( 'send' ) );
		$mailer_mock->expects( $this->exactly( 3 ) )
			->method( 'send' )
			->will( $this->returnValue( true ) );

		$batch = new Prompt_Post_Email_Batch( new Prompt_Post_Rendering_Context( $this->data->prompt_post->id() ) );

		$post_mailer = new Prompt_Post_Wp_Mailer( $batch, $api_mock, array( $mailer_mock, 'send' ) );
		$post_mailer->send();

		Prompt_Core::$options->reset();
	}

	function verifyChunkMetadata( $data ) {

		$this->assertArrayHasKey( 'metadata', $data );

		$this->assertEquals( 'prompt/post_mailing/send_notifications', $data['metadata'][0] );

		$this->assertEquals( $this->data->prompt_post->id(), $data['metadata'][1][0] );

		return true;
	}

	function testClearFailures() {

		$recipient = $this->factory->user->create_and_get();

		$post = $this->factory->post->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post );

		$batch_mock = $this->getMock( 'Prompt_Post_Email_Batch', array( 'get_individual_message_values', 'clear_failures' ), array( $context ) );

		$individual_values = array( array( 'id' => $recipient->ID, 'to_address' => $recipient->user_email ) );

		$batch_mock->expects( $this->any() )
			->method( 'get_individual_message_values' )
			->willReturn( $individual_values );

		$batch_mock->expects( $this->once() )
			->method( 'clear_failures' )
			->with( array( $recipient->user_email ) );

		$api_mock = $this->getMock( 'Prompt_Api_Client' );

		$mailer = new Prompt_Post_Wp_Mailer( $batch_mock, $api_mock, '__return_false' );
		$mailer->send();
	}
}
