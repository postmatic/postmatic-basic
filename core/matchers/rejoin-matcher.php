<?php
/**
 * Determine if text is considered a rejoin request.
 * @since 2.0.0
 */
class Prompt_Rejoin_Matcher extends Prompt_Matcher {
	/**
	 * @since 2.0.0
	 * @return string
	 */
	public static function target() {
		/* translators: this is the word used to re-subscribe to comments after flood control has kicked in */
		return __( 'rejoin', 'Postmatic' );
	}

	/**
	 * @since 2.0.0
	 * @return boolean  Whether the text matches a rejoin request
	 */
	public function matches() {

		$pattern = '/^r[ej][ej][io][io][jn]/i';

		return (bool) preg_match( $pattern, $this->stripped_text() );
	}

}