<?php

class PostMailerTest extends WP_UnitTestCase {

	protected $data;

	function setUp() {
		parent::setUp();
		$this->data = new stdClass();
	}

	function testPostNotifications() {
		Prompt_Core::$options->set( 'email_transport', Prompt_Enum_Email_Transports::API );
		$this->data->author = $this->factory->user->create_and_get( array( 'role' => 'author' ) );
		wp_set_current_user( $this->data->author->ID );

		$this->data->author_subscriber = $this->factory->user->create_and_get();
		$site_subscriber = $this->factory->user->create_and_get();
		$post_subscriber = $this->factory->user->create_and_get();
		$this->data->title = 'Test & Title';
		$this->data->content = 'XXXCONTENTXXX';
		$this->data->batch_id = 1;
		$post = $this->factory->post->create_and_get( array(
			'post_title' => $this->data->title,
			'post_status' => 'draft',
			'post_author' => $this->data->author->ID,
			'post_content' => $this->data->content,
		) );

		$prompt_site = new Prompt_Site;
		$prompt_post = new Prompt_Post( $post->ID );
		$prompt_author = new Prompt_User( $this->data->author->ID );

		$prompt_author->subscribe( $this->data->author_subscriber->ID );
		$prompt_site->subscribe( $site_subscriber->ID );
		$prompt_post->subscribe( $post_subscriber->ID );

		$batch = new Prompt_Post_Email_Batch( new Prompt_Post_Rendering_Context( $post ) );

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'post_outbound_message_batches' )
			->will( $this->returnCallback( array( $this, 'verifyOutboundBatch' ) ) );

		$post_mailer = new Prompt_Post_Mailer( $batch, $api_mock );
		$post_mailer->send();

		// Multiple sends should have no effect
		$post_mailer->send();

		$this->assertEquals( array( $this->data->batch_id ), $prompt_post->outbound_message_batch_ids() );

		wp_set_current_user( 0 );
		Prompt_Core::$options->reset();
	}

	/**
	 * @param array $data
	 * @return array Simulated request response including JSON with batch ID
	 */
	function verifyOutboundBatch( $data ) {

		$this->assertContains(
			$this->data->title,
			$data['batch_message_template']['subject'],
			'Expected template subject to contain title.'
		);

		$this->assertNotContains(
			'Error:',
			$data['batch_message_template']['html_content'],
			'Expected no error notifications.'
		);

		$this->assertNotContains(
			'Warning:',
			$data['batch_message_template']['html_content'],
			'Expected no error notifications.'
		);

		$this->assertContains(
			get_option( 'blogname' ),
			$data['default_values']['from_name'],
			'Expected from name to contain blogname.'
		);

		$this->assertContains(
			'{{{from_name}}}',
			$data['batch_message_template']['from_name'],
			'Expected from name to contain blogname.'
		);

		$this->assertEquals(
			Prompt_Enum_Message_Types::POST,
			$data['batch_message_template']['message_type'],
			'Expected post message type.'
		);

		$this->assertCount( 2, $data['individual_message_values'], 'Expected two notification recipients.' );

		foreach( $data['individual_message_values'] as $message_values ) {

			$this->assertArrayHasKey( 'to_address', $message_values );
			$this->assertArrayHasKey( 'trackable-address', $message_values['reply_to'] );

		}

		return array(
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'body' => json_encode( array( 'id' => $this->data->batch_id ) ),
		);
	}

	function testNoSubscribersNoEmail() {
		$post = $this->factory->post->create_and_get();
		$batch = new Prompt_Post_Email_Batch( new Prompt_Post_Rendering_Context( $post ) );
		$batch->add_unsent_recipients();

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->never() )->method( 'post_outbound_message_batches' );

		$mailer = new Prompt_Post_Mailer( $batch, $api_mock );
		$mailer->send();

		$prompt_post = new Prompt_Post( $post );
		$this->assertEmpty( $prompt_post->outbound_message_batch_ids(), 'Expected no outbound batch ids.' );
	}

	function testNoBatchRecordedOnApiError() {
		$prompt_post = new Prompt_Post( $this->factory->post->create_and_get() );
		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $this->factory->user->create() );

		$batch = new Prompt_Post_Email_Batch( new Prompt_Post_Rendering_Context( $prompt_post->id() ) );
		$batch->add_unsent_recipients();

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'post_outbound_message_batches' )
			->will( $this->returnValue( new WP_Error() ) );

		$mailer = new Prompt_Post_Mailer( $batch, $api_mock );

		$mailer->send();

		$this->assertEmpty( $prompt_post->outbound_message_batch_ids(), 'Expected no outbound batch ids.' );
	}
}
