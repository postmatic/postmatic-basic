<?php

/**
 * A simple HTML view renderer.
 */
class Prompt_Template {

	/** @var  string */
	protected $dir;
	/** @var  string */
	protected $name;

	/**
	 * Instantiate an HTML template.
	 *
	 * @since 2.0.0
	 * @param string $name
	 * @param string|null $dir
	 */
	public function __construct( $name, $dir = null ) {
		$this->name = $name;
		$this->dir = $dir ? $dir : path_join( Prompt_Core::$dir_path, 'templates/html' );
	}

	/**
	 * Search the active theme for a template, falling back to the plugin template.
	 *
	 * Templates are are sought first in a 'prompt' subdirectory, then the theme
	 * root. If none are found, the plugin default is used.
	 *
	 * @return string Selected template.
	 */
	public function locate() {

		// First choice is a template in the theme root or prompt subdirectory
		$template_names = array(
			'postmatic/' . $this->name,
			'prompt/' . $this->name,
			$this->name,
		);
		$template = locate_template( $template_names );

		// For local mailing look for an inlined template first
		if ( ! $template and ! Prompt_Core::is_api_transport() ) {
			$template = path_join( $this->dir . '-inlined', $this->name );
		}

		// Fallback is the core or provided directory
		if ( ! $template or ! file_exists( $template ) ) {
			$template = path_join( $this->dir, $this->name );
		}

		return $template;
	}

	/**
	 * Render a template with an array of data in scope.
	 *
	 * @param array $data An array of data to provide to the template
	 * @param boolean $echo Whether to echo output, default false
	 * @return string Rendered output
	 */
	public function render( $data = array(), $echo = false ) {
		$output = '';
		$template = $this->locate();
		if ( $template ) {
			extract( $data );
			if ( !$echo )
				ob_start();
			require( $template );
			if ( !$echo )
				$output = ob_get_clean();
		}
		return $output;
	}
}