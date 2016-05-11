<?php

/**
 * Pass significant occurrences on to the events API.
 */
class Prompt_Event_Handling {

	public static function record_deactivation() {

		$key = Prompt_Core::$options->get( 'prompt_key' );
		if ( !$key )
			return;

		self::record_event( time(), 'activated', compact( 'key' ), $key );

	}

	public static function record_reactivation() {

		// Core options not yet available
		$options = get_option( 'prompt_options' );

		if ( !$options )
			return;

		$key = $options['prompt_key'];

		if ( !$key )
			return;

		self::record_event( time(), 'activated', compact( 'key' ), $key );

	}

	public static function record_environment() {

		$environment = new Prompt_Environment();

		self::record_event( time(), 'environment', $environment->to_array() );

	}

	protected static function record_event( $timestamp, $code, $data, $key = '', $url = '' ) {

		$key = $key ? $key : Prompt_Core::$options->get( 'prompt_key' );

		$default_url = defined( 'PROMPT_EVENT_API_URL' ) ? PROMPT_EVENT_API_URL : 'https://events.gopostmatic.com/api/v1';

		$url = $url ? $url : $default_url;

		$client = new Prompt_Api_Client( array(), $key, $url );

		$data = array( 'events' => array( compact( 'timestamp', 'code', 'data' ) ) );

		$client->post_events( $data );

	}
}