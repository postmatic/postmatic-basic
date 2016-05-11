<?php

class Prompt_Custom_HTML_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	public function __construct() {
		$this->add_filter( 'the_content', array( $this, 'use_customized_content' ), 1, 1 );
	}

	/**
	 * @since 2.0.0
	 *
	 * @param string $content
	 * @return string
	 */
	public function use_customized_content( $content ) {
		
		$prompt_post = new Prompt_Post( get_the_ID() );
		
		$custom_html = $prompt_post->get_custom_html();
		
		return $custom_html ? $custom_html : $content;
	}
}