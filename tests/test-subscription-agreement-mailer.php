<?php

class SubscriptionAgreementMailerTest extends WP_UnitTestCase {

	protected $data;

	function setUp() {
		parent::setUp();
		$this->data = new stdClass();
	}

	function testAgreementsRetry() {
		add_filter( 'prompt/make_rescheduler', array( $this, 'mockAgreementsRescheduler' ) );

		$object = new Prompt_Site;
		$users = array(
			array( 'user_email' => 'test1@example.com' ),
			array( 'user_email' => 'test2@example.com' ),
		);
		$template_data = array();
		$batch = new Prompt_Subscription_Agreement_Email_Batch( $object, $template_data );
		$batch->add_agreement_recipients( $users );

		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'post_outbound_message_batches' )
			->will( $this->returnValue( new WP_Error( 'test', 'Failed to connect' ) ) );

		$mailer = new Prompt_Subscription_Agreement_Mailer( $batch, $api_mock );
		$mailer->send();

		$this->assertTrue( isset( $this->data->mock_rescheduler ), 'Expected a mock rescheduler to be created.' );

		remove_filter( 'prompt/make_rescheduler', array( $this, 'mockAgreementsRescheduler' ) );
	}

	function mockAgreementsRescheduler( $real_rescheduler ) {
		return $this->mockRescheduler( 'prompt/subscription_mailing/send_agreements' );
	}

	function mockRescheduler( $hook ) {
		$mock = $this->getMockBuilder( 'Prompt_Rescheduler' )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->once() )
			->method( 'found_temporary_error' )
			->will( $this->returnValue( true ) );

		$mock->expects( $this->once() )
			->method( 'reschedule' )
			->with( $this->equalTo( $hook ), $this->anything() );

		$this->data->mock_rescheduler = $mock;
		return $mock;
	}

}
