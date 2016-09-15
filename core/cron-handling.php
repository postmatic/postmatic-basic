<?php

/**
 * Handle wp-cron manipulations.
 * @since 2.0.11
 */
class Prompt_Cron_Handling {

	/**
	 * Clear all Postmatic scheduled cron jobs.
	 *
	 * @since 2.0.11
	 */
	public static function clear_all() {
		wp_unschedule_hook( 'prompt/post_mailing/send_notifications' );
		wp_unschedule_hook( 'prompt/comment_mailing/send_notifications' );
		wp_unschedule_hook( 'prompt/subscription_mailing/send_agreements' );
		wp_unschedule_hook( 'prompt/inbound_handling/pull_updates' );
		wp_unschedule_hook( 'prompt/inbound_handling/acknowledge_updates' );
	}

	/**
	 * @since 2.0.11
	 * @param string $hook
	 */
	protected static function unschedule_hook( $hook ) {

		if ( function_exists( 'wp_unschedule_hook' ) ) {
			wp_unschedule_hook( $hook ) ;
			return;
		}

		$crons = _get_cron_array();

		foreach ( $crons as $timestamp => $args ) {

			unset( $crons[$timestamp][$hook] );

			if ( empty( $crons[$timestamp] ) ) {
				unset( $crons[$timestamp] );
			}

		}

		_set_cron_array( $crons );
	}
}