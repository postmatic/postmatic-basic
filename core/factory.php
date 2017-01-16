<?php

class Prompt_Factory {

	/**
	 * Create an instance of a Prompt object with sensible defaults.
	 * @param string $thing
	 * @param array $args
	 * @return mixed
	 */
	public static function make( $thing, $args = array() ) {
		return call_user_func_array( array( __CLASS__, 'make_' . strtolower( $thing ) ), $args );
	}

	/**
	 * Make a mailer instance appropriate to the environment.
	 * @since 2.0.0
	 * @param Prompt_Email_Batch $batch
	 * @param string $transport Optional transport to use
	 * @param int $chunk Optional chunk number for local mailer, default 0
	 * @return Prompt_Mailer
	 */
	public static function make_mailer( $batch, $transport = null, $chunk = 0 ) {

		$mailer = self::is_transport_api( $transport ) ?
			self::make_api_mailer( $batch ) :
			self::make_local_mailer( $batch, $chunk );

		$mailer = apply_filters( 'prompt/make_mailer', $mailer, $batch, $transport, $chunk );

		return $mailer;
	}

	/**
	 * Make a mailer instance appropriate to the environment for a post preview or ad hoc request.
	 *
	 * These cases don't need scheduling or idempotent checks, so we can use a standard mailer.
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Email_Batch $batch
	 * @param string $transport Optional transport to use
	 * @return Prompt_Mailer
	 */
	public static function make_post_adhoc_mailer( $batch, $transport = null ) {

		$mailer = self::is_transport_api( $transport ) ? new Prompt_Mailer( $batch ) : new Prompt_Wp_Mailer( $batch );

		$mailer = apply_filters( 'prompt/make_mailer', $mailer, $batch, $transport );

		return $mailer;
	}

	/**
	 * @since 2.0.0
	 * @param Prompt_Email_Batch $batch
	 * @return Prompt_Mailer
	 */
	protected static function make_api_mailer( $batch ) {

		if ( is_a( $batch, 'Prompt_Comment_Email_Batch' ) ) {
			return new Prompt_Comment_Mailer( $batch );
		}

		if ( is_a( $batch, 'Prompt_Subscription_Agreement_Email_Batch' ) ) {
			return new Prompt_Subscription_Agreement_Mailer( $batch );
		}

		return new Prompt_Mailer( $batch );
	}

	/**
	 * @since 2.0.0
	 * @param Prompt_Email_Batch $batch
	 * @param int $chunk
	 * @return Prompt_Mailer
	 */
	protected static function make_local_mailer( $batch, $chunk = 0 ) {

		if ( is_a( $batch, 'Prompt_Comment_Email_Batch' ) ) {
			return new Prompt_Comment_Wp_Mailer( $batch, null, null, $chunk );
		}

		if ( is_a( $batch, 'Prompt_Subscription_Agreement_Email_Batch' ) ) {
			return new Prompt_Subscription_Agreement_Wp_Mailer( $batch, null, null, $chunk );
		}

		return new Prompt_Wp_Mailer( $batch, null, null, $chunk );
	}

	/**
	 * @param Prompt_Api_Client $client Optional API client instance.
	 * @return Prompt_Inbound_Messenger
	 */
	public static function make_inbound_messenger( Prompt_Api_Client $client = null ) {
		return apply_filters( 'prompt/make_inbound_messenger', new Prompt_Inbound_Messenger( $client ) );
	}

	/**
	 * @param Prompt_Api_Client $client Optional API client instance.
	 * @return Prompt_Configurator
	 */
	public static function make_configurator( Prompt_Api_Client $client = null ) {
		return apply_filters( 'prompt/make_configurator', new Prompt_Configurator( $client ) );
	}

	/**
	 * @return Prompt_Admin_Jetpack_Import
	 */
	public static function make_jetpack_import() {
		return apply_filters( 'prompt/make_jetpack_import', Prompt_Admin_Jetpack_Import::make() );
	}

	/**
	 * @since 1.4.0
	 *
	 * @param array|WP_Error $job_result
	 * @param int $wait_seconds
	 * @return Prompt_Rescheduler
	 */
	public static function make_rescheduler( $job_result, $wait_seconds ) {
		return apply_filters( 'prompt/make_rescheduler', new Prompt_Rescheduler( $job_result, $wait_seconds ) );
	}

	/**
	 * @since 2.0.0
	 * @param object $comment
	 * @return Prompt_Comment_Flood_Controller
	 */
	public static function make_comment_flood_controller( $comment ) {
		return apply_filters( 'prompt/make_comment_flood_controller', new Prompt_Comment_Flood_Controller( $comment ), $comment );
	}

	/**
	 * Shorten checks for current email transport.
	 * @since 2.0.0
	 *
	 * @param string $transport Optional value to use instead of the option.
	 * @return boolean
	 */
	protected static function is_transport_api( $transport = null ) {
		$transport = $transport ? $transport : Prompt_Core::$options->get( 'email_transport' );

		return ( Prompt_Enum_Email_Transports::API == $transport );
	}
}