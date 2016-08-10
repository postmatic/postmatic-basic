<?php

/**
 * Modify post content to escape handlebars expressions.
 * @since 2.0.10
 */
class Prompt_Handlebars_Escape_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	/** @var  Prompt_Handlebars */
	protected $handlebars;

	/**
	 * @since 2.0.10
	 * @param null|Prompt_Handlebars Optional handlebars instance.
	 */
	public function __construct( $handlebars = null ) {
		$this->handlebars = $handlebars ? $handlebars : new Prompt_Handlebars();
		$this->add_filter( 'the_content', array( $this, 'escape_handlebars_expressions' ), 1, 1 );
		$this->add_filter( 'the_excerpt', array( $this, 'escape_handlebars_expressions' ), 1, 1 );
		$this->add_filter( 'the_title', array( $this, 'escape_handlebars_expressions' ), 1, 1 );
	}

	/**
	 * Escape handlebars expressions.
	 * @since 2.0.10
	 *
	 * @param string $content
	 * @return string
	 */
	public function escape_handlebars_expressions( $content ) {
		return $this->handlebars->escape_expressions( $content );
	}
}