<?php

/**
 * Responsible for retrying failed mailing jobs.
 *
 * @since 2.0.0
 */
class Prompt_Mailing {

	/**
	 * @since 2.0.0
	 *
	 * @param \Prompt_Email_Batch
	 * @param int $retry_wait_seconds
	 */
	public static function send( $batch, $retry_wait_seconds ) {
		Prompt_Factory::make_mailer( $batch )->set_retry_wait_seconds( $retry_wait_seconds )->send();
	}

}