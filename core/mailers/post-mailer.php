<?php

/**
 * Manage sending notifications for a post.
 *
 * @since 2.0.0
 *
 */
class Prompt_Post_Mailer extends Prompt_Mailer {

	/** @var  Prompt_Post_Email_Batch */
	protected $batch;

	/**
	 * @since 2.0.0
	 *
	 * @param Prompt_Post_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client $client
	 */
	public function __construct(
		Prompt_Post_Email_Batch $batch,
		Prompt_Interface_Http_Client $client = null
	) {
		parent::__construct( $batch, $client );
	}

	/**
	 * Add idempotent checks and batch recording to the parent send method.
	 *
	 * @since 2.0.0
	 *
	 * @return null|object|WP_Error
	 */
	public function send() {

		$this->batch->set_individual_message_values( array() )->add_unsent_recipients()->lock_for_sending();

		$result = parent::send();

		if ( $result and ! is_wp_error( $result ) ) {
			$this->record_successful_outbound_message_batch( $result );
		}

		return $result;
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
				'prompt/post_mailing/send_notifications',
				array( $this->batch->get_context()->get_post()->id(), 'reschedule' )
			);
			return true;
		}

		return false;
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param object $data
	 */
	protected function record_successful_outbound_message_batch( $data ) {

		if ( empty( $data->id ) ) {
			Prompt_Logging::add_error(
				Prompt_Enum_Error_Codes::OUTBOUND,
				__( 'Got an unrecognized outbound message batch response.', 'Postmatic' ),
				array( 'result' => $data, 'post_id' => $this->batch->get_context()->get_post()->id() )
			);
			return;
		}

		$this->batch->get_context()->get_post()->add_outbound_message_batch_ids( $data->id );
	}
}