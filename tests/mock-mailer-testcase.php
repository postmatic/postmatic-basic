<?php

class Prompt_MockMailerTestCase extends Prompt_UnitTestCase {

	protected $mailer_expects;
	protected $mailer_method;
	protected $mailer_will;
	/** @var  Prompt_Email_Batch */
	protected $mailer_payload;

	/** @var  object */
	protected $mail_data;
	/** @var  string Email transport mailer was initialized with */
	protected $transport;

	function setUp() {
		parent::setUp();

		$this->mailer_expects = $this->once();
		$this->mailer_method = 'send';
		$this->mailer_will = $this->returnValue( true );

		$this->mail_data = new stdClass();

		add_filter( 'prompt/make_mailer', array( $this, 'get_mock_mailer' ), 10, 3 );
	}

	function tearDown() {
		parent::tearDown();

		remove_filter( 'prompt/make_mailer', array( $this, 'get_mock_mailer' ), 10 );
	}

	function get_mock_mailer( $mailer, $payload, $transport ) {
		$this->transport = $transport;

		$this->mailer_payload = $payload;

		$mock = $this->getMock(
			get_class( $mailer ),
			array( $this->mailer_method ),
			array( $payload )
		);

		$mock->expects( $this->mailer_expects )
			->method( $this->mailer_method )
			->will( $this->mailer_will );

		return $mock;
	}

	function verifyErrorFreeContent() {
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertNotContains( 'Notice:', $template['html_content'], 'Expected no PHP notices in message.' );
		$this->assertNotContains( 'Notice:', $template['text_content'], 'Expected no PHP notices in message.' );
		$this->assertNotContains( 'Error:', $template['html_content'], 'Expected no PHP errors in message.' );
		$this->assertNotContains( 'Error:', $template['text_content'], 'Expected no PHP errors in message.' );
	}

	function verifySubscriptionEmail( $subject_text ) {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->subscriber->user_email, $values[0]['to_address'] );
		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains( $subject_text, $template['subject'] );
		$this->verifyErrorFreeContent();
	}

	function verifySubscribedEmail() {
		$this->verifySubscriptionEmail( ' subscribed' );
	}

	function verifyUnsubscribedEmail() {
		$this->verifySubscriptionEmail( ' unsubscribed' );
	}


}