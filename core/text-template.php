<?php

/**
 * A template that converts HTML content to Markdown.
 *
 * @since 2.0.0
 *
 */
class Prompt_Text_Template extends Prompt_Template {

	/**
	 * Convert HTML data to Markdown before rendering.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data
	 * @param bool|false $echo
	 * @return string rendered text
	 */
	public function render( $data = array(), $echo = false ) {

		foreach ( $data as $key => $value ) {
			$data[$key] = $this->convert_html_string_to_markdown( $value );
		}

		return parent::render( $data, $echo );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param $value
	 * @return string
	 */
	protected function convert_html_string_to_markdown( $value ) {
		if ( is_string( $value ) and strpos( $value, '<' ) !== false ) {
			return Prompt_Html_To_Markdown::convert( $value );
		}
		return $value;
	}
}