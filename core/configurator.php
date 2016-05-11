<?php

class Prompt_Configurator {

	protected $client;

	public function __construct( Prompt_Interface_Http_Client $client = null ) {
		$this->client = $client ? $client : new Prompt_Api_Client();
	}

	/**
	 * Check for site updates.
	 * @return boolean|WP_Error status
	 */
	public function pull_configuration() {

		$response = $this->client->get_site();

		if ( is_wp_error( $response ) or 200 != $response['response']['code'] )
			return Prompt_Logging::add_error(
				'pull_configuration_http',
				__( 'A request for site configuration failed.', 'Postmatic' ),
				$response
			);

		$data = json_decode( $response['body'] );

		if ( !isset( $data->site ) )
			return Prompt_Logging::add_error(
				'pull_configuration_site_missing',
				__( 'Configuration data arrived in an unrecognized format.', 'Postmatic' ),
				$data
			);

		return $this->update_configuration( $data );
	}

	public function update_configuration( $data ) {

		Prompt_Core::$options->set( 'messages', $data->messages );
		Prompt_Core::$options->set( (array) $data->configuration );

		return true;
	}

}