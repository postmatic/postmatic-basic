<?php

/**
 * Handle making and scheduling requests for inbound messages.
 *
 * @since 1.3.0
 */
class Prompt_Inbound_Handling {

	/**
	 * Make a reschedulable request for updates. Acknowledges requests when successful.
	 *
	 * @since 1.3.0
	 *
	 * @param int $retry_wait_seconds Optional minimum time to wait if a retry is necessary, or null to disable retry
	 * @return bool|WP_Error status
	 */
	public static function pull_updates( $retry_wait_seconds = 60 ) {

		$messenger = Prompt_Factory::make_inbound_messenger();

		$updates = $messenger->pull_updates();

		$rescheduler = new Prompt_Rescheduler( $updates, $retry_wait_seconds );

		if ( $rescheduler->found_temporary_error() ) {

			$rescheduler->reschedule( 'prompt/inbound_handling/pull_updates' );

			return $updates;
		}

		if ( is_wp_error( $updates ) )
			return Prompt_Logging::add_wp_error( $updates );

		return self::acknowledge_updates( $updates );
	}

	/**
	 * Make a reschedulable request to acknowledge updates.
	 *
	 * @since 1.3.0
	 *
	 * @param array $updates Array as returned by pull_updates
	 * @param int $retry_wait_seconds Optional. One second default as this is a time-sensitive request.
	 * @return bool|WP_Error status
	 */
	public static function acknowledge_updates( $updates, $retry_wait_seconds = 1 ) {

		$messenger = Prompt_Factory::make_inbound_messenger();

		$result = $messenger->acknowledge_updates( $updates );

		$rescheduler = new Prompt_Rescheduler( $result, $retry_wait_seconds );

		if ( $rescheduler->found_temporary_error() ) {

			$rescheduler->reschedule( 'prompt/inbound_handling/acknowledge_updates', array( $updates ) );

			return $result;
		}

		if ( is_wp_error( $result ) )
			Prompt_Logging::add_wp_error( $result );

		return $result;
	}

}