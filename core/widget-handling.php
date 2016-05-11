<?php

class Prompt_Widget_Handling {

	/**
	 * Register widgets and widget areas.
	 */
	public static function register() {

		if ( ! Prompt_Core::$options->get( 'prompt_key' ) )
			return;

		register_widget( 'Prompt_Subscribe_Widget' );

	}
}
