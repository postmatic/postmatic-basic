<?php

/**
 * Responsible for sending generic emails via the Postmatic API.
 *
 * Primarily a base class. Though it will work while the deprecated API is in service,
 * only subclasses are still used by the plugin.
 *
 * @since 2.0.0
 */
class Prompt_Mailer implements Prompt_Interface_Reschedulable {

	/** @var string  */
	protected static $retry_option = 'prompt_mailer_retries';

	/** @var Prompt_Email_Batch */
	protected $batch;
	/** @var bool */
	protected $is_retry = false;
	/** @var int */
	protected $retry_wait_seconds = 60;
	/** @var \Prompt_Api_Client|\Prompt_Interface_Http_Client  */
	protected $client;

	/**
	 * @since 2.0.0
	 *
	 * @param Prompt_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client|null $client
	 */
	public function __construct( Prompt_Email_Batch $batch, Prompt_Interface_Http_Client $client = null ) {
		$this->batch = $batch;

		$this->client = $client ? $client : new Prompt_Api_Client();
	}

	/**
	 * @since 2.0.0
	 *
	 * @param int|null $seconds
	 * @return Prompt_Mailer $this
	 */
	public function set_retry_wait_seconds( $seconds = null ) {
		if ( ! is_null( $seconds ) ) {
			$this->retry_wait_seconds = absint( $seconds );
			$this->is_retry = true;
		}
		return $this;
	}

	/**
	 * @since 2.0.0
	 *
	 * @return null|object|WP_Error
	 */
	public function send() {

		do_action( 'prompt/outbound/batch', $this->batch );

		if ( ! $this->batch->get_individual_message_values() ) {
			return null;
		}

		if ( $this->is_retry and $this->already_retried() ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::DUPLICATE,
				__( 'Duplicate retry skipped.', 'Postmatic' ),
				array( 'batch' => $this->batch, 'retry_wait_seconds' => $this->retry_wait_seconds )
			);
		}

		$result = $this->client->post_outbound_message_batches( $this->batch->to_array() );

		if ( $this->reschedule( $result ) ) {
			return $result;
		}

		$error = $this->translate_error( $result );

		if ( $error ) {
			return $error;
		}

		return json_decode( $result['body'] );
	}

	/**
	 * Detect if this retry attempt has already been made.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function already_retried() {
		$log = get_option( self::$retry_option, array() );
		$key = md5( $this->retry_wait_seconds . serialize( $this->batch->to_array() ) );
		if ( isset( $log[$key] ) ) {
			return true;
		}
		$log[$key] = microtime();
		update_option( self::$retry_option, $log, false );
		return false;
	}

	/**
	 * Detect error responses and translate to a WP_Error if needed.
	 *
	 * @since 2.0.0
	 *
	 * @param array $response
	 * @return null|WP_Error
	 */
	protected function translate_error( $response ) {

		if ( is_wp_error( $response ) ){
			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::API,
				wp_remote_retrieve_response_message( $response ),
				array( 'response' => $response, 'batch' => $this->batch )
			);
		}

		return null;
	}

	/**
	 * Schedule a retry if a temporary failure has occurred.
	 *
	 * @since 2.0.0
	 *
	 * @param array $response
	 * @return bool Whether a retry has been rescheduled.
	 */
	protected function reschedule( $response ) {

		$rescheduler = Prompt_Factory::make_rescheduler( $response, $this->retry_wait_seconds );

		if ( $rescheduler->found_temporary_error() ) {
			$rescheduler->reschedule(
				'prompt/mailing/send',
				array( 'batch' => $this->batch )
			);
			return true;
		}

		return false;
	}

}