<?php

/**
 * Manage output formatting for Postmatic emails.
 * @since 2.0.10
 */

class Prompt_Formatting {

	/**
	 * @since 2.0.10
	 * @param string $string
	 * @return string
	 */
	public static function escape_handlebars_expressions( $string ) {
		return preg_replace( '/(\{{2,3}[^\}]*\}{2,3})/', '\\\$1', $string );
	}
}