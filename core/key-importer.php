<?php

/**
 * Key Importer
 * @since 2.1.0
 */
class Prompt_Key_Importer {

	/** @var  Prompt_Options */
	protected $options;
	/** @var  Prompt_Api_Client */
	protected $client;

	/**
	 * Instantiate a key importer.
	 *
	 * @since 2.1.0
	 * @param Prompt_Options $options
	 * @param Prompt_Api_Client $client
	 */
	public function __construct( Prompt_Options $options, Prompt_Api_Client $client ) {
		$this->options = $options;
		$this->client = $client;
	}

	/**
	 * Set a new key if there is not one and the new one verifies.
	 *
	 * @since 2.1.0
	 * @param string $key
	 * @return bool|WP_Error
	 */
	public function import( $key ) {

		if ( $this->options->get( 'prompt_key' ) ) {
			return new WP_Error( 'prompt_key_import_exists', 'Cannot replace key.' );
		}

		$check = $this->client->get_site();

		if ( is_wp_error( $check ) ) {
			return $check;
		}

		if ( !isset( $check['response']['code'] ) or $check['response']['code'] != 200 ) {
			return new WP_Error( 'prompt_key_import_unverified', 'The new key was not verfied.' );
		}

		$this->options->set( 'prompt_key', $key );

		return true;
	}
}