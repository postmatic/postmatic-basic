<?php
/**
 * Manage handlebars usage.
 */

/**
 * Decorate the handlebars library.
 * @since 1.4.3
 */
class Prompt_Handlebars extends Handlebars_Engine {

	/**
	 * @since 1.4.3
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Render a handlebars template with a given context.
	 * @since 1.4.3
	 * @param string $string
	 * @param array $context
	 * @return string
	 */
	public function render_string( $string, $context ) {
		return $this->loadString( $string )->render( $context );
	}

}
