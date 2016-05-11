<?php

class Prompt_Rescheduler {

	/** @var mixed|WP_Error */
	protected $job_result;
	/** @var int */
	protected $wait_seconds;

	/**
	 * @since 1.3.0
	 *
	 * @param array|WP_Error $job_result
	 * @param int $wait_seconds
	 */
	public function __construct( $job_result, $wait_seconds ) {
		$this->job_result = $job_result;
		$this->wait_seconds = $wait_seconds;
	}

	/**
	 * Determine if rescheduling is suggested based on the job result.
	 *
	 * @since 1.3.0
	 *
	 * @return bool
	 */
	public function found_temporary_error() {

		if ( $this->is_service_unavailable() ) {
			return true;
		}

		if ( ! is_wp_error( $this->job_result ) ) {
			return false;
		}

		if ( 'http_request_failed' != $this->job_result->get_error_code() ) {
			return false;
		}

		$error_message_patterns = array(
			'Failed to connect',
			'Couldn\'t resolve host',
			'name lookup timed out',
			'couldn\'t connect to host',
			'Connection refused',
			'Empty reply from server',
		);

		$error_message_pattern = '/(' . implode( '|', $error_message_patterns ) . ')/';

		if ( ! preg_match( $error_message_pattern, $this->job_result->get_error_message() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Reschedule a job on an exponential decay timing scheme that increases wait times by a factor of 4.
	 *
	 * @since 1.3.0
	 *
	 * @param string $hook The hook to run
	 * @param array $args The arguments minus retry_wait_seconds, which will be calculated as the last argument.
	 */
	public function reschedule( $hook, $args = array() ) {

		if ( is_null( $this->wait_seconds ) ) {
			Prompt_Logging::add_wp_error( $this->job_result );
			return;
		}

		// Add a random factor to spread things out a little
		$retry_time = time() + $this->wait_seconds + rand( 0, $this->wait_seconds / 50 );

		$next_wait = $this->wait_seconds * 4;

		$one_day_in_seconds = 60*60*24;

		if ( $next_wait > $one_day_in_seconds )
			$next_wait = null;

		$args[] = $next_wait;

		wp_schedule_single_event( $retry_time, $hook, $args );
	}

	/**
	 * Whether the job result indicates that the service is unavailable and will return.
	 *
	 * @since 1.4.10
	 *
	 * @return boolean
	 */
	protected function is_service_unavailable() {

		$data = is_wp_error( $this->job_result ) ? $this->job_result->get_error_data() : $this->job_result;

		if ( !is_array( $data ) ) {
			return false;
		}

		if ( !isset( $data['response']['code'] ) ) {
			return false;
		}

		return ( 503 == $data['response']['code'] );
	}
}