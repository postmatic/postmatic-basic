<?php

class Prompt_Request_Signature {

	/** @var int */
	protected $timestamp;
	/** @var string */
	protected $token;
	/** @var string */
	protected $signature;
	/** @var string */
	protected $key;

	public function __construct( $key, $timestamp = null, $token = null ) {
		$this->key = $key;
		$this->timestamp = $timestamp ? intval( $timestamp ) : time();
		$this->token = $token ? sanitize_key( $token ) : md5( date( 'c' ) );
	}

	/**
	 * @since 2.0.0
	 * @return int
	 */
	public function get_timestamp() {
		return $this->timestamp;
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function get_signature() {
		if ( ! $this->signature ) {
			$this->signature = hash_hmac( 'sha256', $this->get_timestamp() . $this->get_token(), $this->key );
		}
		return $this->signature;
	}

	/**
	 * @since 2.0.0
	 * @param string $signature
	 * @return bool|WP_Error
	 */
	public function validate( $signature ) {

		if ( abs( time() - $this->timestamp ) > 6 * HOUR_IN_SECONDS ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::SIGNATURE,
				'Request signature has an invalid timestamp.',
				array( 'signature' => $signature, 'timestamp' => $this->timestamp, 'token' => $this->token )
			);
		}

		if ( strlen( $signature ) != 64 or $signature != $this->get_signature() ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::SIGNATURE,
				'Request signature is invalid.',
				array( 'signature' => $signature, 'timestamp' => $this->timestamp, 'token' => $this->token )
			);
		}

		return true;
	}


}