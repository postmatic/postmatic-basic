<?php

class MailerTest extends WP_UnitTestCase {

	protected $data;

	function test_email_api() {

		$batch_mock = $this->getMock( 'Prompt_Email_Batch' );
		$batch_mock->expects( $this->once() )
			->method( 'get_individual_message_values' )
			->willReturn( true );

		$batch_array = array( 'foo' => 'bar' );
		$batch_mock->expects( $this->once() )
			->method( 'to_array' )
			->willReturn( $batch_array );

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'post_outbound_message_batches' )
			->with( $batch_array );

		$mailer = new Prompt_Mailer( $batch_mock, $api_mock );

		$mailer->send();
	}

	function test_batch_action() {
		add_action( 'prompt/outbound/batch', array( $this, 'clear_batch' ) );

		$batch = new Prompt_Email_Batch(
			array(
				'subject' => 'Test Subject',
				'html' => 'Test Message',
			),
			array( 'to_address' => 'test@example.com' )
		);

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->never() )->method( 'post_outbound_message_batches' );

		$mailer = new Prompt_Mailer( $batch, $api_mock );

		$this->assertNull( $mailer->send(), 'Expected send to return null.' );

		remove_action( 'prompt/outbound/batch', array( $this, 'clear_batch' ) );
	}

	function clear_batch( Prompt_Email_Batch $batch ) {
		$batch->set_individual_message_values( array() );
	}

}