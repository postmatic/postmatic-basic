<?php

/**
 * Handle API requests via the WP ajax interface.
 * @since 1.0.0
 */
class Prompt_Web_Api_Handling {

	/**
	 * Receive an ajax API pull updates request.
	 * @since 1.0.0
	 */
	public static function receive_pull_updates() {

		self::validate_or_die();

		self::close_connection();

		$result = Prompt_Inbound_Handling::pull_updates();

		self::set_return_code_and_die( $result );
	}

	/**
	 * Receive an ajax API pull configuration request.
	 * @since 1.0.0
	 */
	public static function receive_pull_configuration() {

		self::validate_or_die();

		self::close_connection();

		$result = Prompt_Configuration_Handling::pull_configuration();

		self::set_return_code_and_die( $result );
	}

	/**
	 * Receive an ajax API callback request.
	 * @since 1.0.0
	 */
	public static function receive_callback() {

		self::validate_or_die();

		self::close_connection();

		$metadata = self::get_callback_metadata_or_die();

		do_action_ref_array( $metadata[0], $metadata[1] );

		self::set_return_code_and_die( 200 );
	}

	/**
	 * Receive a ping request.
	 *
	 * @since 1.0.0
	 */
	public static function receive_ping() {

		self::validate_or_die();

		wp_send_json_success();
	}

	/**
	 * Receive a key request.
	 *
	 * @since 2.1.0
	 */
	public static function receive_key() {

		if ( ! isset( $_POST['key'] ) ) {
			status_header( 400 );
			wp_die();
		}

		$key = sanitize_text_field( $_POST['key'] );

		$importer = new Prompt_Key_Importer( $key, Prompt_Core::$options, new Prompt_Api_Client( array(), $key ) );

		$result = $importer->import();

		if ( is_wp_error( $result ) ) {
			status_header( 400 );
			Prompt_Logging::add_wp_error( $result );
		}

		wp_die();
	}

	/**
	 * Try to close the connection but allow processing to continue.
	 * @since 2.0.0
	 */
	public static function close_connection() {
		// http://stackoverflow.com/questions/138374/close-a-connection-early
		header( 'Content-Length: 0' );
		header( 'Connection: close' );
		ob_end_flush();
		if ( ob_get_level() > 0 ) {
			ob_flush();
		}
		flush();
	}

	/**
	 * @since 1.0.0
	 */
	protected static function validate_or_die() {
		if ( !self::validate_request() ) {
			status_header( 401 );
			wp_die();
		}
	}

	/**
	 * @since 1.0.0
	 * @param int|WP_Error $status
	 */
	protected static function set_return_code_and_die( $status ) {
		if ( is_wp_error( $status ) )
			status_header( 500 );

		wp_die();
	}

	/**
	 * @return array metadata
	 */
	protected static function get_callback_metadata_or_die() {
		if ( !isset( $_GET['metadata'] ) ) {
			status_header( 400 );
			wp_die();
		}

		// There's an extra level of slashes to remove here originating in json_encode()
		$metadata = wp_unslash( json_decode( wp_unslash( $_GET['metadata'] ), $assoc = true ) );

		if ( count( $metadata ) != 2 ) {
			status_header( 400 );
			wp_die();
		}

		$hook_name = explode( '/', $metadata[0] );

		if ( !in_array( $hook_name[0], array( 'prompt', 'postmatic' ) ) ) {
			status_header( 400 );
			wp_die();
		}

		return $metadata;
	}

	/**
	 * @since 2.0.0
	 * @return bool Whether request is valid.
	 */
	protected static function validate_request() {

		$timestamp = intval( $_GET['timestamp'] );
		$token = sanitize_key( $_GET['token'] );

		$signature = new Prompt_Request_Signature( Prompt_Core::$options->get( 'prompt_key' ), $timestamp, $token );

		$validity = $signature->validate( $_GET['signature'] );

		if ( is_wp_error( $validity ) ) {
			Prompt_Logging::add_wp_error( $validity );
			return false;
		}

		if ( Prompt_Enum_Connection_Status::CONNECTED != Prompt_Core::$options->get( 'connection_status' ) ) {
			Prompt_Core::$options->set( 'connection_status', Prompt_Enum_Connection_Status::CONNECTED );
		}

		return true;
	}

}