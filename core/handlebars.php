<?php

require_once( path_join( Prompt_Core::$dir_path, 'vendor/xamin/handlebars.php/src/Handlebars/Autoloader.php' ) );

Handlebars_Autoloader::register();

/**
 * Decorate the handlebars library.
 */
class Prompt_Handlebars extends Handlebars_Engine {

	public function __construct() {
		parent::__construct();
	}

	public function render_string( $string, $context ) {
		return $this->loadString( $string )->render( $context );
	}
}
