<?php

/**
 * Base class for options page notices.
 *
 * @since 1.2.3
 */
class Prompt_Admin_Conditional_Notice {

	/** @var  string Key to a boolean option indicating this notice has been dismissed */
	protected $skip_option_key;

	/**
	 * Register a click of the dismiss button for this option.
	 *
	 * @since 1.2.3
	 */
	public function process_dismissal() {
		if ( isset( $_GET[ $this->skip_option_key ] ) ) {
			$this->dismiss();
		}
	}

	/**
	 * Record notice dismissal.
	 *
	 * @since 1.2.3
	 */
	public function dismiss() {
		Prompt_Core::$options->set( $this->skip_option_key, true );
	}

	/**
	 * Show a dismissed notice again.
	 *
	 * @since 1.2.3
	 */
	public function undismiss() {
		Prompt_Core::$options->set( $this->skip_option_key, false );
	}

	/**
	 * Whether the notice has been dismissed.
	 *
	 * @since 1.2.3
	 *
	 * @return bool
	 */
	public function is_dismissed() {
		return Prompt_Core::$options->get( $this->skip_option_key );
	}

	/**
	 * Display (echo) the notice if conditions are met.
	 *
	 * Does nothing if conditions fail or the notice has been dismissed.
	 *
	 * @since 1.2.3
	 */
	public function maybe_display() {
		if ( !$this->is_dismissed() )
			echo $this->render();
	}

	/**
	 * Render the notice as a string.
	 *
	 * May be empty if the conditions are not met. This would be abstract in PHP > 5.2.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function render() {
		return $this->render_message( 'Hello!' );
	}

	/**
	 * Render a message string in an error notice div.
	 *
	 * @since 1.2.3
	 *
	 * @param $message
	 * @return string
	 */
	protected function render_message( $message ) {
		return html( 'div class="notice error"',
			html( 'p', $message, '&nbsp;', $this->render_dismiss_link() )
		);
	}

	/**
	 * Render a dismiss link for the notice.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	protected function render_dismiss_link() {
		return html( 'a',
			array( 'href' => esc_url( $this->dismiss_url() ), 'class' => 'button' ),
			__( 'Dismiss', 'Postmatic' )
		);
	}

	/**
	 * The current URL with a dismissal argument added.
	 * @since 2.0.0
	 * @return string
	 */
	protected function dismiss_url() {
		return add_query_arg( $this->skip_option_key, 'true' );
	}
}