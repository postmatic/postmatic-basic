<?php

/**
 * Handle making and scheduling requests for configuration changes.
 *
 * @since 2.0.0
 */
class Prompt_Configuration_Handling {

	/**
	 * Make a reschedule enabled request for current configuration.
	 *
	 * @since 2.0.0
	 *
	 * @param int $retry_wait_seconds Optional minimum time to wait if a retry is necessary, or null to disable retry
	 * @return bool|WP_Error status
	 */
	public static function pull_configuration( $retry_wait_seconds = 60 ) {

		$result = Prompt_Factory::make_configurator()->pull_configuration();

		$rescheduler = new Prompt_Rescheduler( $result, $retry_wait_seconds );

		if ( $rescheduler->found_temporary_error() ) {

			$rescheduler->reschedule( 'prompt/configuration_handling/pull_configuration' );

			return $result;
		}

		if ( is_wp_error( $result ) ) {
			return Prompt_Logging::add_wp_error( $result );
		}

		return $result;
	}

}