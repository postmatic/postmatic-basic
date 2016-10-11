<?php
/**
 * Determine if text is considered an unsubscribe request.
 *
 * @since 2.0.0
 *
 */
class Prompt_Unsubscribe_Matcher extends Prompt_Matcher {

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function target() {
		/* translators: this is the word used to unsubscribe via email reply */
		return __( 'unsubscribe', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return boolean  Whether the text matches an unsubscribe request
	 */
	public function matches() {

		$unsubscribe_pattern = sprintf(
			'/^(%s|un..[bn]sc?ri?be?|sunsubscribe|unsusbscribe|un..scribe|unsusribe?|unsubcribe)/i',
			self::target()
		);

		return (bool) preg_match( $unsubscribe_pattern, $this->stripped_text() );
	}

}