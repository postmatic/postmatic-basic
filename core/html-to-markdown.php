<?php

class Prompt_Html_To_Markdown {

	/**
	 * In addition to markdown conversion, strips images and treats h3-6 as paragraphs.
	 * @param string $html
	 * @return string empty if conversion fails
	 */
	public static function convert( $html ) {
		$converter = new HTML_To_Markdown();

		$html = preg_replace( '/<img[^>]*>/', '', $html );

		$html = preg_replace( '/<(\/)?h[3-6]>/', '<$1p>', $html );

		$converter->set_option( 'header_style', 'postmatic' );
		$converter->set_option( 'strip_tags', true );

		$markdown = $converter->convert( $html );

		return $markdown ? $markdown : '';
	}

	/**
	 * Get a top level markdown header.
	 * @param string $content
	 * @return string
	 */
	public static function h1( $content ) {
		return self::tag( 'h1', $content );
	}

	/**
	 * Get a second level markdown header.
	 * @param string $content
	 * @return string
	 */
	public static function h2( $content ) {
		return self::tag( 'h2', $content );
	}

	protected static function tag( $tag, $content ) {
		return self::convert( html( $tag, $content ) );
	}
}