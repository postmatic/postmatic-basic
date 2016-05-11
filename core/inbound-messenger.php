<?php

class Prompt_Inbound_Messenger {

	protected $client;

	public function __construct( Prompt_Interface_Http_Client $client = null ) {
		$this->client = $client ? $client : new Prompt_Api_Client();
	}

	/**
	 * Check for new messages.
	 *
	 * @return array|WP_Error Array of new messages or error.
	 */
	public function pull_updates() {

		$response = $this->client->get_undelivered_updates();

		if ( is_wp_error( $response ) )
			return $response;

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::INBOUND,
				wp_remote_retrieve_response_message( $response ),
				$response
			);
		}

		$data = json_decode( $response['body'] );

		if ( !isset( $data->updates ) )
			return new WP_Error(
				Prompt_Enum_Error_Codes::INBOUND,
				__( 'Inbound messages arrived in an unrecognized format.', 'Postmatic' ),
				$data
			);

		if ( ! $data->updates )
			$data->updates = array();

		$result_updates = array();
		foreach ( $data->updates as $update ) {
			$result = array( 'id' => $update->id );
			$result['status'] = $this->process_update( $update );
			$result_updates[] = $result;
		}
		return array( 'updates' => $result_updates );
	}

	/**
	 * Tell the server we have received and processed these updates.
	 *
	 * @since 1.3.0
	 *
	 * @param array $updates
	 * @return bool|WP_Error
	 */
	public function acknowledge_updates( $updates ) {

		if ( !isset( $updates['updates'] ) ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::INBOUND_ACKNOWLEDGE,
				__( 'Failed to acknowledge unrecognized updates response.', 'Postmatic' ),
				array( $updates )
			);
		}

		// No need for a request if there were no updates
		if ( empty( $updates['updates'] ) )
			return true;

		$request = array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => json_encode( $updates ),
		);

		$response = $this->client->put( '/updates', $request );

		if ( is_wp_error( $response ) or 200 != $response['response']['code'] ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::INBOUND_ACKNOWLEDGE,
				__( 'Failed to acknowledge receipt of messages - they may arrive again.', 'Postmatic' ),
				compact( 'response', 'results' )
			);
		}

		return true;
	}

	/**
	 * Process updates according to their type.
	 * @param object $update
	 * @return string Status: 'delivered', 'lost'
	 */
	public function process_update( $update ) {

		if ( 'inbound-email' == $update->type ) {
			return $this->process_inbound_email( $update );
		}

		Prompt_Logging::add_error(
			'unknown_update_type',
			__( 'Unable to deliver a message of unknown type.', 'Postmatic' ),
			$update
		);

		return 'lost';
	}

	/**
	 * Process a incoming email POST request.
	 * @param object $update The email data
	 * @return array Representation of processing results.
	 */
	public function process_inbound_email( $update ) {

		$command = Prompt_Command_Handling::make_command( $update->data );

		if ( !$command ) {
			return null;
		}

		$command->execute();

		return 'delivered';
	}

}