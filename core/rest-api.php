<?php

/**
 * The Postmatic REST API singleton
 * @since 2.0.0
 */
class Prompt_Rest_Api {

	/**
	 * @since 2.0.0
	 * @var Prompt_Rest_Api
	 */
	private static $instance = null;

	/**
	 * @since 2.0.0
	 * @var scbOptions
	 */
	private $options;

	/**
	 * Initialize via the rest_api_init hook.
	 * @since 2.0.0
	 * @param WP_REST_Server $server
	 */
	public static function init( WP_REST_Server $server ) {
		self::get_instance();
	}

	/**
	 * Get the singleton instance.
	 * @since 2.0.0
	 * @param array $args
	 * @return Prompt_Rest_Api
	 */
	public static function get_instance( $args = array() ) {

		if ( isset( $args['reset'] ) or is_null( self::$instance ) ) {
			$defaults = array(
				'options' => Prompt_Core::$options,
			);
			$args = wp_parse_args( $args, $defaults );
			self::$instance = new Prompt_Rest_Api( $args );
		}

		return self::$instance;
	}

	/**
	 * Register routes on construction.
	 * @since 2.0.0
	 * @param array $args
	 */
	private function __construct( $args ) {
		$version = 'v1';
		$namespace = "postmatic/$version";

		$this->options = $args['options'];

		register_rest_route( $namespace, 'invocations', array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'create_invocation' ),
			'permission_callback' => array( $this, 'is_signed' ),
			'args' => array(
				'metadata' => array(
					'required' => true,
					'sanitize_callback' => array( $this, 'sanitize_invocation_metadata' ),
					'validate_callback' => array( $this, 'validate_invocation_metadata' ),
				)
			),
		) );
	}

	/**
	 * Check whether a request is signed with the API key.
	 * @since 2.0.0
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function is_signed( WP_REST_Request $request ) {

		$signature = new Prompt_Request_Signature(
			$this->options->get( 'prompt_key' ),
			$request['timestamp'],
			$request['token']
		);

		$validity = $signature->validate( $request['signature'] );

		if ( is_wp_error( $validity ) ) {
			Prompt_Logging::add_wp_error( $validity );
			$validity->add_data( array( 'status' => 401 ) );
			return $validity;
		}

		if ( Prompt_Enum_Connection_Status::CONNECTED != $this->options->get( 'connection_status' ) ) {
			$this->options->set( 'connection_status', Prompt_Enum_Connection_Status::CONNECTED  );
		}

		return $validity;
	}

	/**
	 * Create a new invocation.
	 * @since 2.0.0
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create_invocation( WP_REST_Request $request ) {
		do_action_ref_array( $request['metadata'][0], $request['metadata'][1] );
		return new WP_REST_Response( null, 204 );
	}

	/**
	 * Decode data from JSON.
	 *
	 * @since 2.0.0
	 * @param string $metadata
	 * @param WP_REST_Request $request
	 * @param string $key
	 * @return array
	 */
	public function sanitize_invocation_metadata( $metadata, $request, $key ) {
		// There's an extra level of slashes to remove here originating in json_encode()
		return wp_unslash( json_decode( wp_unslash( $metadata ), $assoc = true ) );
	}

	/**
	 * Allow only our own hooks.
	 *
	 * @since 2.0.0
	 * @param string $metadata
	 * @param WP_REST_Request $request
	 * @param string $key
	 * @return bool
	 */
	public function validate_invocation_metadata( $metadata, $request, $key ) {

		if ( count( $metadata ) != 2 ) {
			// Require an array with hook name and arguments
			return false;
		}

		$hook_name = explode( '/', $metadata[0] );
		if ( ! in_array( $hook_name[0], array( 'prompt', 'postmatic' ) ) ) {
			// Allow only our own hooks
			return false;
		}

		return true;
	}

}
