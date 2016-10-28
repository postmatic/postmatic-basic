<?php

/**
 * Manage hashing
 * @since 2.1.0
 */
class Prompt_Hasher {
	/** @var  string */
	protected $key;

	/**
	 * Prompt_Hasher constructor.
	 *
	 * @since 2.1.0
	 * @param string $key
	 */
	public function __construct( $key = '' ) {
		$this->key = $key ? $key : uniqid();
	}

	/**
	 * @since 2.1.0
	 * @param string $value
	 * @return string
	 */
	public function hash( $value ) {
		return hash_hmac( 'sha256', $value, $this->key );
	}
}