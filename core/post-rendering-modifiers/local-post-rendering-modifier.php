<?php

class Prompt_Local_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	/** @var  string */
	protected $original_content;
	/** @var  string */
	protected $original_excerpt;

	public function __construct() {
		$this->add_filter( 'the_content', array( $this, 'set_original_content' ), 1, 1 );
		$this->add_filter( 'the_excerpt', array( $this, 'set_original_excerpt' ), 1, 1 );
		$this->add_filter( 'the_content', array( $this, 'override_content_filters' ), 9999, 1 );
		$this->add_filter( 'the_excerpt', array( $this, 'override_excerpt_filters' ), 9999, 1 );
	}

	/**
	 * @since 1.4.0
	 *
	 * @param string $content
	 * @return string
	 */
	public function set_original_content( $content ) {
		$this->original_content = $content;
		return $content;
	}

	/**
	 * @since 1.4.0
	 *
	 * @param string $excerpt
	 * @return string
	 */
	public function set_original_excerpt( $excerpt ) {
		$this->original_excerpt = $excerpt;
		return $excerpt;
	}

	/**
	 * @since 1.4.0
	 *
	 * @param $content
	 * @return string content with only our own filters applied
	 */
	public function override_content_filters( $content ) {
		return wpautop( wptexturize( $this->strip_fancy_content( $this->original_content ) ) );
	}

	/**
	 * @since 1.4.0
	 *
	 * @param $excerpt
	 * @return string
	 */
	public function override_excerpt_filters( $excerpt ) {
		return wp_trim_excerpt( wpautop( wptexturize( $this->original_excerpt ) ) );
	}

	/**
	 * Remove all images, shortcodes, iframes, objects, and other fancy stuff.
	 *
	 * Makes unlinked URIs clickable.
	 *
	 * @param string $content
	 * @return string
	 */
	public function strip_fancy_content( $content ) {

		// strip shortcodes
		$content = strip_shortcodes( $content );

		// strip iframes and objects
		$content = preg_replace( '#<(iframe|object)[^>]*>.*?<\\/\\1>#', '', $content );

		// make unlinked URLs clickable
		$content = make_clickable( $content );

		return $content;
	}

}