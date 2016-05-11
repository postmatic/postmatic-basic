<?php

/**
 * Base functionality for a something that changes how a post is rendered for mailing.
 *
 * Extend this class to implement specific rendering modifications, using the add_*
 * and remove_* methods in the child class constructor.
 */
class Prompt_Post_Rendering_Modifier {

	protected $added_actions = array();
	protected $removed_actions = array();
	protected $added_filters = array();
	protected $removed_filters = array();
	protected $added_shortcodes = array();
	protected $removed_shortcodes = array();

	/**
	 * Prepare for rendering by adding and removing filters, actions, and shortcodes.
	 *
	 * @since 1.4.0
	 */
	public function setup() {

		$this->call_for_each( 'remove_filter', $this->removed_filters, $drop_last = true );
		$this->call_for_each( 'add_filter', $this->added_filters );
		$this->call_for_each( 'remove_action', $this->removed_actions, $drop_last = true );
		$this->call_for_each( 'add_action', $this->added_actions );
		// For shortcodes it's important to remove first, so a shortcode can be replaced
		$this->call_for_each( 'remove_shortcode', $this->removed_shortcodes, $drop_last = true );
		$this->call_for_each( 'add_shortcode', $this->added_shortcodes );

	}

	/**
	 * Undo the setup after rendering.
	 *
	 * @since 1.4.0
	 */
	public function reset() {

		$this->call_for_each( 'add_filter', $this->removed_filters );
		$this->call_for_each( 'remove_filter', $this->added_filters, $drop_last = true );
		$this->call_for_each( 'add_action', $this->removed_actions );
		$this->call_for_each( 'remove_action', $this->added_actions, $drop_last = true );
		$this->call_for_each( 'add_shortcode', $this->removed_shortcodes );
		$this->call_for_each( 'remove_shortcode', $this->added_shortcodes, $drop_last = true );

	}

	/**
	 * Queue a filter to be added.
	 *
	 * @since 1.4.0
	 */
	protected function add_filter() {
		$args = func_get_args();
		$this->added_filters[] = $args;
	}

	/**
	 * Queue a filter to be removed.
	 *
	 * @since 1.4.0
	 */
	protected function remove_filter() {
		$args = func_get_args();
		$this->removed_filters[] = $args;
	}

	/**
	 * Queue an action to be added.
	 *
	 * @since 1.4.0
	 */
	protected function add_action() {
		$args = func_get_args();
		$this->added_actions[] = $args;
	}

	/**
	 * Queue an action to be removed.
	 *
	 * @since 1.4.0
	 */
	protected function remove_action() {
		$args = func_get_args();
		$this->removed_actions[] = $args;
	}

	/**
	 * Queue a shortcode to be added.
	 *
	 * @since 1.4.0
	 */
	protected function add_shortcode() {
		$args = func_get_args();
		$this->added_shortcodes[] = $args;
	}

	/**
	 * Queue a filter to be removed.
	 *
	 * @since 1.4.0
	 */
	protected function remove_shortcode() {
		$args = func_get_args();
		$this->removed_shortcodes[] = $args;
	}

	protected function call_for_each( $func, $args_array, $drop_last = false ) {
		foreach ( $args_array as $args ) {
			$this->call_array( $func, $args, $drop_last );
		}
	}

	protected function call_array( $func, $args, $drop_last = false ) {
		if ( $drop_last )
			$args = array_slice( $args, 0, count( $args ) - 1 );
		call_user_func_array( $func, $args );
	}

	/**
	 * Get markup for a placeholder for incompatible content.
	 *
	 * There is a modifier just for incompatible content, but others encounter it also.
	 *
	 * @since 1.4.0
	 *
	 * @param string $class
	 * @param null $url
	 * @return string
	 */
	protected function incompatible_placeholder( $class = '', $url = null ) {
		$class = 'incompatible' . ( $class ? ' ' . $class : '' );
		$url = $url ? $url : get_permalink();
		return html( 'div',
			array( 'class' => $class ),
			__( 'This content is not compatible with your email client. ', 'Postmatic' ),
			html( 'a',
				array( 'href' => $url ),
			__( 'Click here to view this content in your browser.', 'Postmatic' )
			)
		);
	}

}