<?php

class ReschedulerTest extends WP_UnitTestCase {

	protected $data;

	function setup() {
		parent::setUp();
		$this->data = new stdClass();
	}

	function testNoError() {
		$rescheduler = new Prompt_Rescheduler( array(), 1 );
		$this->assertFalse( $rescheduler->found_temporary_error(), 'Expected no error to be found' );
	}

	function testPermanentError() {
		$rescheduler = new Prompt_Rescheduler( new WP_Error( 'test_error', 'Something timed out.' ), 1 );
		$this->assertFalse( $rescheduler->found_temporary_error(), 'Expected no error to be found' );
	}

	/**
	 * @dataProvider temporaryErrorProvider
	 */
	function testTemporaryErrors( $error ) {
		$rescheduler = new Prompt_Rescheduler( $error, 1 );
		$this->assertTrue( $rescheduler->found_temporary_error(), 'Expected a temporary error to be found' );
	}

	function temporaryErrorProvider() {
		return array(
			array( new WP_Error( 'http_request_failed', "Couldn't resolve host 'app.gopostmatic.com'" ) ),
			array( new WP_Error( 'http_request_failed', "Failed to connect to app.gopostmatic.com port 443: Connection timed out" ) ),
			array( new WP_Error( 'http_request_failed', "name lookup timed out" ) ),
			array( new WP_Error( 'http_request_failed', "couldn't connect to host" ) ),
			array( new WP_Error( 'http_request_failed', "Failed connect to localhost:5000; Connection refused" ) ),
			array( new WP_Error( 'http_request_failed', "Empty reply from server" ) ),
			array( new WP_Error(
				'api_error',
				'Service Unavailable',
				array(
					'headers' => array(),
					'body' => 'Maintenance mode',
					'response' => array( 'code' => 503, 'message' => 'Service Unavailable' ),
				)
			) ),
			array(
				array(
					'headers' => array(),
					'body' => 'Maintenance mode',
					'response' => array( 'code' => 503, 'message' => 'Service Unavailable' ),
				)
			),
		);
	}

	function testReschedule() {
		$error = new WP_Error( 'http_request_failed', "Couldn't resolve host 'app.gopostmatic.com'" );
		$this->data->hook = 'test/hook';
		$this->data->verified = false;
		$this->data->time = time();
		$this->data->wait = 100;
		$this->data->args = array( 'testarg' );
		add_filter( 'schedule_event', array( $this, 'verifyReschedule' ) );

		$rescheduler = new Prompt_Rescheduler( $error, $this->data->wait );

		$rescheduler->reschedule( $this->data->hook, $this->data->args );

		$this->assertTrue( $this->data->verified, 'Expected job rescheduling.' );

		remove_filter( 'schedule_event', array( $this, 'verifyReschedule' ) );
	}

	function verifyReschedule( $event ) {
		$this->assertEquals( $this->data->hook, $event->hook );
		$this->assertGreaterThan( $this->data->time, $event->timestamp );
		$this->assertLessThan( $this->data->time + $this->data->wait * 2, $event->timestamp );
		$this->data->args[] = $this->data->wait * 4;
		$this->assertEquals( $this->data->args, $event->args );
		$this->data->verified = true;
	}

	function testLogError() {
		$error = new WP_Error( 'http_request_failed', "Couldn't resolve host 'app.gopostmatic.com'" );
		$this->data->rescheduled = false;
		$this->data->logged = false;
		add_filter( 'schedule_event', array( $this, 'verifyNoReschedule' ) );
		add_filter( 'pre_update_option_prompt_log', array( $this, 'verifyLogging' ) );

		$rescheduler = new Prompt_Rescheduler( $error, 100 );


		$this->assertFalse( $this->data->rescheduled, 'Expected no job rescheduling.' );
		$this->assertFalse( $this->data->logged, 'Expected error to be logged.' );

		remove_filter( 'pre_update_option_prompt_log', array( $this, 'verifyLogging' ) );
		remove_filter( 'schedule_event', array( $this, 'verifyNoReschedule' ) );
	}

	function verifyNoReschedule( $event ) {
		$this->data->rescheduled = true;
	}

	function verifyLogging( $value ) {
		$this->assertCount( 1, $value, 'Expected a log entry.' );
		$this->data->logged = true;
	}
}