<?php
/**
 * Determine if text is considered a site post subscription request.
 *
 * @since 2.0.0
 *
 */
class Prompt_Instant_Matcher extends Prompt_Matcher {
	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function target() {
		/* translators: this is the word used to switch to or select a new post subscription via email reply */
		return __( 'instant', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return boolean  Whether the text matches an unsubscribe request
	 */
	public function matches() {

		$pattern = sprintf( '/^(%s|[sia][sian][sn]ta?[tn]{2,3})/i', self::target() );

		return (bool) preg_match( $pattern, $this->stripped_text() );
	}

}