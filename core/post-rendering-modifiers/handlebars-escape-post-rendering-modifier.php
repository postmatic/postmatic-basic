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
	public function __construct() {
		$this->add_filter( 'the_content', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ), 10, 1 );
		$this->add_filter( 'the_excerpt', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ), 10, 1 );
		$this->add_filter( 'the_title', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ), 10, 1 );
	}

}