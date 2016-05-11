<?php

/**
 * Modify rendering of the post excerpt for email.
 *
 * @since 2.0.0
 *
 */
class Prompt_Excerpt_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	public function __construct() {
		$this->add_filter( 'excerpt_more', array( $this, 'remove_excerpt_more' ), 11, 1 );
		$this->add_filter( 'wp_trim_excerpt', array( $this, 'end_with_sentence' ), 11, 1 );
	}

	/**
	 * @param string $more_text
	 * @return string
	 */
	public function remove_excerpt_more( $more_text ) {
		return '';
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function end_with_sentence( $text ) {
		if ( preg_match( '/^(.*)[\.\!\?]\s/', $text, $matches ) ) {
			$text = $matches[0];
		}
		return $text;
	}
}
