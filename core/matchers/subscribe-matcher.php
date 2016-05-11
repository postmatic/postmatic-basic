<?php
/**
 * Determine if text is considered a subscribe request.
 *
 * @since 2.0.0
 *
 */
class Prompt_Subscribe_Matcher extends Prompt_Matcher {
	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function target() {
		/* translators: this is the word used to request a subscription via email reply */
		return __( 'subscribe', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return boolean  Whether the text matches a subscribe request
	 */
	public function matches() {

		$subscribe_pattern = sprintf( '/^(%s|usbscribe|s..scribe|suscribe|susribe?|susrib)/i', self::target() );

		return (bool) preg_match( $subscribe_pattern, $this->stripped_text() );
	}

}