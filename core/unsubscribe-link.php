<?php

class Prompt_Unsubscribe_Link {

	protected static $email_key = 'email';
	protected static $token_key = 't';
	protected static $signature_key = 's';
	protected static $view_key = 'postmatic_view';
	protected static $view = 'unsubscribe';

	protected $args;
	protected $user;

	/**
	 * @param WP_User|array $user_or_args A user for a new link, or query arguments of a visited link
	 */
	public function __construct( $user_or_args ) {

		if ( is_a( $user_or_args, 'WP_User' ) ) {
			$this->user = $user_or_args;
			return;
		}

		if ( ! is_array( $user_or_args ) )
			return;

		$this->args = $user_or_args;
	}

	/**
	 * @return bool True if the supplied arguments are valid
	 */
	public function is_valid() {

		if ( ! $this->user )
			$this->parse();

		return (bool) $this->user;
	}

	/**
	 * @return WP_User|null The user to unsubscribe or null for an invalid request
	 */
	public function user() {

		if ( ! $this->user )
			$this->parse();

		return $this->user;
	}

	/**
	 * @return string A valid unsubscribe URL for the given user
	 */
	public function url() {

		if ( ! $this->args )
			$this->generate_args();

		return add_query_arg( $this->args, Prompt_View_Handling::view_url( 'unsubscribe' ) );
	}

	protected function parse() {

		if (
			empty( $this->args[self::$email_key] ) or
			empty( $this->args[self::$token_key] ) or
			empty( $this->args[self::$signature_key] )
		)
			return;

		$email = sanitize_email( $this->args[self::$email_key] );
		$token = sanitize_text_field( $this->args[self::$token_key] );
		$signature = sanitize_text_field( $this->args[self::$signature_key] );

		$check_signature = $this->hash( $token . $email );

		if ( $check_signature != $signature )
			return;

		$this->user = get_user_by( 'email', $email );
	}

	protected function generate_args() {

		$email = $this->user->user_email;
		$token = $this->hash( uniqid() );
		$signature = $this->hash( $token . $email );

		$this->args = array(
			self::$email_key => urlencode( $email ),
			self::$token_key => $token,
			self::$signature_key => $signature,
		);
	}

	protected function hash( $text ) {
		return substr( hash_hmac( 'sha256', $text, Prompt_Core::$options->get( 'prompt_key' ) ), 0, 16 );
	}
}