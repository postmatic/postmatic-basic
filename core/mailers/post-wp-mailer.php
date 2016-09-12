<?php

/**
 * Manage sending notifications for a post locally.
 *
 * @since 2.0.0
 *
 */
class Prompt_Post_Wp_Mailer extends Prompt_Wp_Mailer {

	/** @var  Prompt_Post_Email_Batch */
	protected $batch;
	/** @var bool */
	protected $rescheduled = false;

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Post_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client|null $client
	 * @param callable|null $local_mailer
	 */
	public function __construct(
		Prompt_Post_Email_Batch $batch,
		Prompt_Interface_Http_Client $client = null,
		$local_mailer = null
	) {
		parent::__construct( $batch, $client, $local_mailer );
	}

	/**
	 * Send email notifications for a post.
	 *
	 * Sends up to emails_per_chunk unsent notifications, and schedules another chunk if there are more.
	 */
	public function send() {

		$recipient_values = $this->batch->add_unsent_recipients()->get_individual_message_values();

		$chunks = array_chunk( $recipient_values, absint( Prompt_Core::$options->get( 'emails_per_chunk' ) ) );

		if ( empty( $chunks[0] ) )
			return null;

		$chunk = $chunks[0];

		/**
		 * Filter whether to send new post notifications. Default true.
		 *
		 * @param boolean $send Whether to send notifications.
		 * @param WP_Post $post
		 * @param array $recipient_ids
		 */
		if ( !apply_filters( 'prompt/send_post_notifications', true, $this->batch->get_context()->get_post(), $chunk ) )
			return null;

		$this->batch->set_individual_message_values( $chunk )->lock_for_sending();

		$result = parent::send();

		if ( ! $this->rescheduled and ! empty( $chunks[1] ) ) {
			$this->schedule_next_chunk();
		}

		if ( ! $this->rescheduled ) {
			$this->clear_failures( $result );
		}

		return $result;
	}

	/**
	 * @since 2.0.11
	 * @param array $result send() result array
	 */
	protected function clear_failures( $result ) {

		$not_function = create_function( '$a', 'return !$a;' );

		$failed_addresses = array_keys( array_filter( $result, $not_function ) );

		if ( empty( $failed_addresses ) ) {
			return;
		}

		$this->batch->clear_failures( $failed_addresses );
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

			$this->batch->clear_for_retry();

			$rescheduler->reschedule(
				'prompt/post_mailing/send_notifications',
				array( $this->batch->get_context()->get_post()->id(), 'reschedule' )
			);

			return $this->rescheduled = true;
		}

		return false;
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 */
	protected function schedule_next_chunk() {

		$this->client->post_instant_callback(
			array(
				'metadata' => array(
					'prompt/post_mailing/send_notifications',
					array( $this->batch->get_context()->get_post()->id() ),
				),
			)
		);

	}
}