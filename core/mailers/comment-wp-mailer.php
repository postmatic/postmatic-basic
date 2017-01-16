<?php

class Prompt_Comment_Wp_Mailer extends Prompt_Wp_Mailer {

	/** @var  Prompt_Comment_Email_Batch */
	protected $batch;
	/** @var bool  */
	protected $rescheduled = false;

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Comment_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client|null $client
	 * @param callable|null $local_mailer
	 * @param int $chunk
	 */
	public function __construct(
		Prompt_Comment_Email_Batch $batch,
		Prompt_Interface_Http_Client $client = null,
		$local_mailer = null,
		$chunk = 0
	) {
		parent::__construct( $batch, $client, $local_mailer, $chunk );
	}

	/**
	 * Send email notifications for a comment.
	 *
	 * Sends all unsent notifications.
	 */
	public function send() {

		/**
		 * Filter whether to send new comment notifications.
		 *
		 * @param boolean $send Default true.
		 * @param Prompt_Comment_Email_Batch $batch
		 */
		if ( !apply_filters( 'prompt/send_comment_notifications', true, $this->batch ) )
			return null;

		$this->batch->lock_for_sending();

		// Turn off native comment notifications
		add_filter( 'pre_option_comments_notify', create_function( '$a', 'return null;' ) );

		$result = parent::send();

		if ( ! $this->rescheduled ) {
			$this->record_failures( $result );
		}

		return $result;
	}

	/**
	 * @since 2.0.11
	 * @param array $result send() result array
	 */
	protected function record_failures( $result ) {

		$not_function = create_function( '$a', 'return !$a;' );

		$failed_addresses = array_keys( array_filter( $result, $not_function ) );

		if ( empty( $failed_addresses ) ) {
			return;
		}

		$this->batch->record_failures( $failed_addresses );
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
				'prompt/comment_mailing/send_notifications',
				array( $this->batch->get_comment()->id(), 'reschedule' )
			);
			return $this->rescheduled = true;
		}

		return false;
	}

}