<?php
/**
 * Determine if text is considered an agreement.
 *
 * @since 2.0.0
 *
 */
class Prompt_Agree_Matcher extends Prompt_Matcher {
	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function target() {
		/* translators: this is the word used to opt in via email reply */
		return __( 'agree', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return boolean  Whether the text matches a subscribe request
	 */
	public function matches() {

		$pattern = '/(?<!n[o\']t )(' . self::target() . '|age?ree?)/i';

		return (bool) preg_match( $pattern, $this->text );
	}

}