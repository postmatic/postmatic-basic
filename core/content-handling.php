<?php
/**
 * Helpers for handling content.
 *
 * @since 2.0.0
 *
 */

class Prompt_Content_Handling {

	/**
	 * Remove HTML tags and convert entities to UTF-8.
	 *
	 * @since 2.0.0
	 *
	 * @param string $html
	 * @return string
	 */
	public static function reduce_html_to_utf8( $html ) {
		return wp_strip_all_tags( html_entity_decode( $html, ENT_QUOTES, 'UTF-8' ) );
	}

	/**
	 * Reduce HTML only when a text format is specified.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @param string $html
	 * @return string
	 */
	public static function html_or_reduced_utf8( $format, $html ) {
		if ( Prompt_Enum_Content_Types::HTML == $format) {
			return $html;
		}
		return self::reduce_html_to_utf8( $html );
	}
}