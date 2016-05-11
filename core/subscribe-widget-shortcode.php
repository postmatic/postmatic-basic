<?php

class Prompt_Subscribe_Widget_Shortcode {

	public static function render( $attributes ) {

		$defaults = array(
			'title' => '',
			'collect_name' => true,
			'template_path' => null,
			'subscribe_prompt' => null,
			'list' => null,
		);

		$attributes = shortcode_atts( $defaults, $attributes );

		$attributes['collect_name'] = self::attribute_boolean_value( $attributes['collect_name'] );

		if ( $attributes['list'] ) {
			$attributes['list'] = Prompt_Subscribing::make_subscribable_from_slug( $attributes['list'] );
		}

		ob_start();

		the_widget( 'Prompt_Subscribe_Widget', $attributes );

		return ob_get_clean();
	}

	protected static function attribute_boolean_value( $text ) {
		if ( ! $text )
			return false;

		if ( preg_match( '/^(false|no)$/i', $text ) )
			return false;

		return true;
	}

}