<?php

class WpMailerTest extends WP_UnitTestCase {
	private $prepare_reply_to_name = 'Prompt';
	private $prepare_reply_to_address = 'prompt+reply@example.com';
	private $data;
	
	function setUp() {
		parent::setUp(); 
		$this->data = new stdClass();
	}

	function test_batch() {
		$batch_template = array(
			'from_name' => 'From Name',
			'from_address' => 'from@email.org',
			'subject' => 'Test Subject',
			'html_content' => 'Test Message',
			'reply_name' => 'Reply Name',
			'reply_address' => 'reply@email.org',
			'message_type' => Prompt_Enum_Message_Types::POST,
		);
		$batch_values = array(
			array(
				'to_address' => 'test@prompt.vern.al',
				'to_name' => 'To Name',
			)
		);
		$batch = new Prompt_Email_Batch( $batch_template, $batch_values );

		$mailer_mock = $this->get_wp_mail_mock_callable();

		$wp_mailer = new Prompt_Wp_Mailer( $batch, $this->get_unused_api_mock(), $mailer_mock );

		$this->data->to = $batch_values[0]['to_name'] . ' <' . $batch_values[0]['to_address'] . '>';
		$this->data->from_header = 'From: ' . $batch_template['from_name'] . ' <' . $batch_template['from_address'] . '>';
		$this->data->subject = $batch_template['subject'];
		$this->data->message = $batch_template['html_content'];
		
		$result = $wp_mailer->send();

		$this->assertNotContains( false, $result, 'Test email was not sent.' );

	}

	function test_batch_filter() {
		add_action( 'prompt/outbound/batch', array( $this, 'clear_batch' ) );

		$batch = Prompt_Email_Batch::make_for_single_recipient( array(
			'subject' => 'Test Subject',
			'html_content' => 'Test Message',
			'to_address' => 'test@example.com',
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		) );

		$mailer_mock = $this->getMock( 'AdHoc', array( 'send' ) );
		$mailer_mock->expects( $this->never() )->method( 'send' );

		$wp_mailer = new Prompt_Wp_Mailer( $batch, $this->get_unused_api_mock(), array( $mailer_mock, 'send' ) );

		$results = $wp_mailer->send();

		$this->assertEmpty( $results, 'Expected an empty result.' );

		remove_action( 'prompt/outbound/batch', array( $this, 'clear_batch' ) );
	}

	function clear_batch( Prompt_Email_Batch $batch ) {
		$batch->set_individual_message_values( array() );
	}

	function test_metadata_email() {
		$batch_template = array(
			'subject' => 'tracked email',
			'text_content' => 'test message {{{reply_to}}}',
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		);
		$batch_values = array(
			array(
				'to_address' => 'test@example.com',
				'reply_to' => array( 'trackable-address' => array( 'key' => 'value' ) ),
			)
		);

		$batch = new Prompt_Email_Batch( $batch_template, $batch_values );

		$this->data->to_address = $batch_values[0]['to_address'];
		$this->data->reply_to_header = 'Reply-To: ' . Prompt_Email_Batch::name_address( 
			$this->prepare_reply_to_address,
			$this->prepare_reply_to_name
		);
		
		$mailer_mock = $this->get_wp_mail_mock_callable( array( $this, 'verify_metadata_mail' ) );

		$wp_mailer = new Prompt_Wp_Mailer( $batch, $this->get_prepare_api_mock(), $mailer_mock );

		$this->assertCount( 1, $wp_mailer->send(), 'Metadata email was not sent.' );
	}
	
	function verify_metadata_mail( $to, $subject, $message, $headers = array() ) {
		$this->assertContains(
			'postmatic-ref-1',
			$message,
			'Expected to find the postmatic ref ID.'
		);

		$this->assertContains(
			$this->prepare_reply_to_address,
			$message,
			'Expected the reply_to address in the content.'
		);
		
		$this->assertContainsStringThatStartsWith( 
			'Reply-To: ',
			$headers,
			'Expected a reply-to header.'
		);
	}

	function test_missing_address_error() {
		$batch = new Prompt_Email_Batch(
			array(
				'subject' => 'test',
				'html_content' => 'test message',
				'message_type' => Prompt_Enum_Message_Types::ADMIN,
			),
			array(
				array(
					'to_address' => '',
					'reply_to' => array( 'trackable-address' => array( 1 ) ),
				)
			)
		);

		$wp_mailer = new Prompt_Wp_Mailer( $batch, $this->get_prepare_api_mock() );

		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$result = $wp_mailer->send();

		$this->assertInstanceOf( 'WP_Error', $result, 'Expected an error result.' );
	}

	function test_prepare_error() {
		$batch = new Prompt_Email_Batch(
			array(
				'subject' => 'test',
				'html_content' => 'test message',
				'message_type' => Prompt_Enum_Message_Types::ADMIN,
			),
			array(
				array(
					'to_address' => 'test@example.com',
					'reply_to' => array( 'trackable-address' => array( 1 ) ),
				)
			)
		);

		$prepare_mock = $this->getMock( 'Prompt_Api_Client' );
		$prepare_mock->expects( $this->once() )
			->method( 'post_outbound_messages' )
			->with( $this->objectHasAttribute( 'outboundMessages' ) )
			->will( $this->returnValue( new WP_Error( 'test', 'test error' ) ) );

		$wp_mailer = new Prompt_Wp_Mailer( $batch, $prepare_mock );

		$result = $wp_mailer->send();

		$this->assertInstanceOf( 'WP_Error', $result, 'Expected an error result.' );
	}

	/**
	 * @return Prompt_Api_Client
	 */
	private function get_unused_api_mock() {
		$prepare_mock = $this->getMock( 'Prompt_Api_Client' );
		$prepare_mock->expects( $this->never() )->method( 'send' );
		return $prepare_mock;
	}

	/**
	 * @return Prompt_Api_Client
	 */
	private function get_prepare_api_mock() {
		$prepare_mock = $this->getMock( 'Prompt_Api_Client' );
		$prepare_mock->expects( $this->once() )
			->method( 'post_outbound_messages' )
			->with( $this->objectHasAttribute( 'outboundMessages' ) )
			->will( $this->returnCallback( array( $this, 'mock_prepare_response' ) ) );
		return $prepare_mock;
	}

	private function get_wp_mail_mock_callable( $verify_callback = null ) {
		
		$verify_callback = $verify_callback ? $verify_callback : array( $this, 'verify_wp_mail' );
		$mock = $this->getMock( 'AdHoc', array( 'send' ) );

		$mock->expects( $this->once() )
			->method( 'send' )
			->willReturnCallback( $verify_callback );
		
		return array( $mock, 'send' );
	}
	
	public function verify_wp_mail( $to, $subject, $message, $headers ) {

		$this->assertEquals( $this->data->to, $to, 'Expected a different recipient string.' );

		$this->assertEquals( $this->data->subject, $subject, 'Expected a different subject.' );

		$this->assertContains( 'X-Postmatic-Site-URL: ' .home_url(), $headers, 'Expected a site URL header.' );
		
		$this->assertContains( 
			'Content-Type: ' . Prompt_Enum_Content_Types::HTML . '; charset=UTF-8', 
			$headers, 
			'Expected a HTML content type.' 
		);
		
		if ( isset( $this->data->message ) ) {
			$this->assertContains( $this->data->message, $message, 'Expected to find the message content.' );
		}

		if ( isset( $this->data->from_header ) ) {
			$this->assertContains( $this->data->from_header, $headers, 'Expected to find the From header.' );
		}
		
		if ( isset( $this->data->reply_to_header ) ) {
			$this->assertContains( $this->data->reply_to_header, $headers, 'Expected to find the reply-to header.' );
		}

		$unsubscribe_types = array( Prompt_Enum_Message_Types::COMMENT, Prompt_Enum_Message_Types::POST );
		
		if ( isset( $this->data->message_type ) and in_array( $this->data->message_type, $unsubscribe_types ) ) {
			$this->assertContainsStringThatStartsWith( 
				'List-Unsubscribe: <mailto:', 
				$headers, 
				'Expected an unsubscribe header.' 
			);
		}

		return true;
	}
	
	protected function assertContainsStringThatStartsWith( $text, $array, $message = null ) {
		$message = $message ? $message : 'Expected to find a string starting with ' . $text;
		$found = false;
		foreach ( $array as $item ) {
			$found = ( $found or strpos( $item, $text ) === 0 );
		}
		$this->assertTrue( $found, $message );
	}

	function mock_prepare_response( $data ) {

		for ( $i = 0; $i < count( $data->outboundMessages ); $i += 1 ) {
			$data->outboundMessages[$i]['id'] = $i + 1;
			$data->outboundMessages[$i]['reply_to'] = $this->prepare_reply_to_name .
				' <' . $this->prepare_reply_to_address . '>';
		}

		return array(
			'response' => array(
				'code' => 200,
			),
			'body' => json_encode( $data ),
		);
	}

}


