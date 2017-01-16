<?php

/**
 * Key Importer
 * @since 2.1.0
 */
class Prompt_Key_Importer {

	/** @var  string */
	protected $key;
	/** @var  Prompt_Options */
	protected $options;
	/** @var  Prompt_Api_Client */
	protected $client;

	/**
	 * Instantiate a key importer.
	 *
	 * @since 2.1.0
	 * @param string $key
	 * @param Prompt_Options $options
	 * @param Prompt_Api_Client $client
	 */
	public function __construct( $key, Prompt_Options $options, Prompt_Api_Client $client ) {
		$this->key = $key;
		$this->options = $options;
		$this->client = $client;
	}

	/**
	 * Set a new key if there is not one and the new one verifies.
	 *
	 * @since 2.1.0
	 * @return bool|WP_Error
	 */
	public function import() {

		if ( $this->options->get( 'prompt_key' ) ) {
			return new WP_Error( 'prompt_key_import_exists', 'Cannot replace key.', $this->key );
		}

		$this->options->set( 'prompt_key', $this->key );

		return true;
	}
}