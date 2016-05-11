<?php

/**
 * @since 2.0.0
 */
class Prompt_Subscription_Agreement_Mailer extends Prompt_Mailer {

	/** @var  Prompt_Subscription_Agreement_Email_Batch */
	protected $batch;

	/**
	 * @since 2.0.0
	 *
	 * @param Prompt_Subscription_Agreement_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client $client
	 */
	public function __construct(
		Prompt_Subscription_Agreement_Email_Batch $batch,
		Prompt_Interface_Http_Client $client = null
	) {
		parent::__construct( $batch, $client );
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
				'prompt/subscription_mailing/send_agreements',
				array( $this->batch->get_lists(), $this->batch->get_users_data(), $this->batch->get_message_data(), 0 )
			);
			return true;
		}

		return false;
	}

}
