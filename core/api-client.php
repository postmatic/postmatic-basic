<?php

/**
 * A client for Prompt web services.
 *
 * Decorates the native WordPress HTTP function wp_remote_request().
 *
 * @since 0.1.0
 */
class Prompt_Api_Client implements Prompt_Interface_Http_Client {

	protected $key;
	protected $base_url;
	protected $defaults;
	protected $implementation;

	/**
	 * @see wp_remote_request()
	 * @param array $defaults           Optional defaults applied to all requests, see wp_remote_request().
	 * @param null $key                 Optional Prompt key. Defaults to the saved prompt_key option.
	 * @param null $base_url            Optional base API URL. Defaults to https://api.gopostmatic.com/api/v1.
	 * @param string $implementation    Optional decorator target. Defaults to wp_remote_request.
	 */
	public function __construct(
		$defaults = array(),
		$key = null,
		$base_url = null,
		$implementation = 'wp_remote_request'
	) {
		$default_url = defined( 'PROMPT_API_URL' ) ? PROMPT_API_URL : 'https://app.gopostmatic.com/api/v1';
		$this->key = $key ? $key : Prompt_Core::$options->get( 'prompt_key' );
		$this->base_url = $base_url ? $base_url : $default_url;
		$this->defaults = $defaults;
		$this->implementation = $implementation;
	}

	/**
	 * Get the current site record
	 *
	 * @since 2.0.0
	 *
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function get_site() {
		return $this->get( '/site' );
	}

	/**
	 * Get undelivered updates
	 *
	 * @since 2.0.0
	 *
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function get_undelivered_updates() {
		return $this->get( '/updates/undelivered' );
	}

	/**
	 * Create and send outbound messages individually
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $data JSON, or array will be encoded as JSON
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function post_outbound_messages( $data ) {
		return $this->post( '/outbound_messages', $this->json_request( $data ) );
	}

	/**
	 * Create and send a templated batch of outbound messages
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $data JSON, or array will be encoded as JSON
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function post_outbound_message_batches( $data ) {
		return $this->post( '/outbound_message_batches', $this->json_request( $data ) );
	}

	/**
	 * Create and send events
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $data JSON, or array will be encoded as JSON
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function post_events( $data ) {
		return $this->post( '/events', $this->json_request( $data ) );
	}

	/**
	 * Request an immediate callback
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $data JSON, or array will be encoded as JSON
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function post_instant_callback( $data ) {
		return $this->post( '/instant_callback', $this->json_request( $data ) );
	}

	/**
	 * Retrieve a scheduled callback by ID
	 *
	 * @since 2.0.0
	 *
	 * @param int $id
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function get_scheduled_callback( $id ) {
		return $this->get( '/scheduled_callbacks/' . intval( $id ) );
	}

	/**
	 * Request a scheduled callback
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $data JSON, or array will be encoded as JSON
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function post_scheduled_callbacks( $data ) {
		return $this->post( '/scheduled_callbacks', $this->json_request( $data ) );
	}

	/**
	 * Delete a scheduled callback by ID
	 *
	 * @since 2.0.0
	 *
	 * @param int $id
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function delete_scheduled_callback( $id ) {
		return $this->delete( '/scheduled_callbacks/' . intval( $id ) );
	}

	/**
	 * Make a method agnostic request
	 *
	 * @since 0.1.0
	 *
	 * @param string $endpoint
	 * @param array $request
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function send( $endpoint, $request = array() ) {

		$url = $this->make_url( $endpoint );

		$request = wp_parse_args( $request, $this->defaults );

		if ( !isset( $request['headers'] ) )
			$request['headers'] = array();

		if ( !isset( $request['headers']['Authorization'] ) )
			$request['headers']['Authorization'] = 'Basic ' . base64_encode( 'api:' . $this->key );

		if ( !isset( $request['headers']['X-Prompt-Core-Version'] ) )
			$request['headers']['X-Prompt-Core-Version'] = Prompt_Core::version( $full = true );

		$default_timeout = defined( 'PROMPT_API_TIMEOUT' ) ? PROMPT_API_TIMEOUT : 30;
		if ( !isset( $request['timeout'] ) )
			$request['timeout'] = $default_timeout;

		$reply = call_user_func( $this->implementation, $url, $request );

		if ( !is_wp_error( $reply ) and isset( $reply['response']['code'] ) and 410 == $reply['response']['code'] ) {
			Prompt_Core::$options->set( 'upgrade_required', true );
		}

		return $reply;
	}

	/**
	 * Make a GET request to any endpoint
	 *
	 * @since 0.1.0
	 *
	 * @param string $endpoint
	 * @param array $request
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function get( $endpoint, $request = array() ) {
		$request['method'] = 'GET';
		return $this->send( $endpoint, $request );
	}

	/**
	 * Make a POST request to any endpoint
	 *
	 * @since 0.1.0
	 *
	 * @param string $endpoint
	 * @param array $request
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function post( $endpoint, $request = array() ) {
		$request['method'] = 'POST';

		if ( !isset( $request['headers']['Content-Type'] ) )
			$request['headers']['Content-Type'] = 'application/json';

		return $this->send( $endpoint, $request );
	}

	/**
	 * Make a HEAD request to any endpoint
	 *
	 * @since 0.1.0
	 *
	 * @param string $endpoint
	 * @param array $request
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function head( $endpoint, $request = array() ) {
		$request['method'] = 'HEAD';
		return $this->send( $endpoint, $request );
	}

	/**
	 * Make a PUT request to any endpoint
	 *
	 * @since 0.1.0
	 *
	 * @param string $endpoint
	 * @param array $request
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function put( $endpoint, $request = array() ) {
		$request['method'] = 'PUT';

		if ( !isset( $request['headers']['Content-Type'] ) )
			$request['headers']['Content-Type'] = 'application/json';

		return $this->send( $endpoint, $request );
	}

	/**
	 * Make a DELETE request to any endpoint
	 *
	 * @since 0.1.0
	 *
	 * @param string $endpoint
	 * @param array $request
	 * @return array|WP_Error The implementation return value, a wp_remote_request() array by default.
	 */
	public function delete( $endpoint, $request = array() ) {
		$request['method'] = 'DELETE';
		return $this->send( $endpoint, $request );
	}

	/**
	 * @param string $endpoint
	 * @return string
	 */
	protected function make_url( $endpoint ) {
		if ( empty( $endpoint ) or '/' == $endpoint[0] )
			return $this->base_url . $endpoint;

		return $endpoint;
	}

	/**
	 * Make a JSON request array
	 *
	 * Set the content type and encode the body with the given data.
	 *
	 * @param mixed $data
	 * @return array Args array for wp_remote_request().
	 */
	protected function json_request( $data ) {
		if ( ! is_string( $data ) )
			$data = json_encode( $data );

		return array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => $data,
		);
	}
}