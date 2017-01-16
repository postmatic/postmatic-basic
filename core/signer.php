<?php

/**
 * Prompt Signer
 *
 * @since 2.1.0
 */
class Prompt_Signer {

	/** @var string token parameter */
	protected static $token_key = 't';
	/** @var string signature parameter */
	protected static $signature_key = 's';

	/** @var  Prompt_Hasher */
	protected $hasher;

	/**
	 * Instantiate a signer.
	 *
	 * @since 2.1.0
	 * @param Prompt_Hasher $hasher
	 */
	public function __construct( Prompt_Hasher $hasher ) {
		$this->hasher = $hasher;
	}

	/**
	 * @since 2.1.0
	 * @param string $base_url
	 * @return string The given base URL with signature query arguments added
	 */
	public function sign_url( $base_url, $data ) {
		return add_query_arg( $this->sign_data( $data ), $base_url );
	}

	/**
	 * @since 2.1.0
	 * @param array $data
	 * @return array Data with token and signature arguments added
	 */
	public function sign_data( $data ) {

		$args = array_map( 'urlencode', $data );

		$token = $this->hasher->hash( uniqid() );
		$signature = $this->hasher->hash( $token . implode( '', $data ) );

		return array_merge(
			$args,
			array(
				self::$token_key => $token,
				self::$signature_key => $signature,
			)
		);
	}

	/**
	 * Whether the data includes a valid signature.
	 *
	 * @since 2.1.0
	 * @param array $data Signed data querystring parameters
	 * @return bool
	 */
	public function is_valid( $data ) {

		$data = array_map( 'sanitize_text_field', $data );

		if ( empty( $data[self::$token_key] ) or empty( $data[self::$signature_key] ) ) {
			return false;
		}

		$token = $data[self::$token_key];
		unset( $data[self::$token_key] );

		$signature = $data[self::$signature_key];
		unset( $data[self::$signature_key] );

		$hash = $this->hasher->hash( $token . implode( '', $data ) );

		return $signature == $hash;
	}

}