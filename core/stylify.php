<?php
/**
 * Manage custom style integration with Stylify
 *
 * @since 2.0.0
 *
 */
class Prompt_Stylify {

	protected $styles;

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param array $styles An array of site styles, ala the site_styles option
	 */
	public function __construct( $styles = array() ) {
		$this->styles = $styles;
	}

	/**
	 * Get the current style array.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_styles() {
		return $this->styles;
	}

	/**
	 * Get a single style value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $selector
	 * @param string $property
	 * @param string $fallback
	 * @return string
	 */
	public function get_value( $selector, $property, $fallback = '' ) {
		 return isset( $this->styles[$selector][$property] ) ? $this->styles[$selector][$property] : $fallback;
	}

	/**
	 * Get a CSS string for postmatic emails based on the current color palette.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_css() {

		$rules = array();

		$a_color = $this->get_value( 'a', 'color' );
		$css = $a_color ? 'a { color: ' . $a_color . '; }' : '';
		if ( $a_color ) {
			$rules[] = 'a { color: ' . $a_color . '; }';
		}

		$rules[] = $this->header_css_rule( 'body', 'table.body', 'body' );
		$rules[] = $this->header_css_rule( '.postmatic-header', 'h1' );
		$rules[] = $this->header_css_rule( '.site-title', 'h1' );
        $rules[] = $this->header_css_rule( '.postmatic-header h1', 'a', 'h1' );
        $rules[] = $this->header_css_rule( '.digest h2.post-title', 'a', 'h2' );
        $rules[] = $this->header_css_rule( '.brand', 'h2', 'h1' );
        $rules[] = $this->header_css_rule( '.postmatic-content', 'a', 'a' );
		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) {
			$rules[] = $this->header_css_rule( '.postmatic-content', $tag );
		}

		$rules[] = $this->header_css_rule( '.postmatic-content', 'blockquote', 'h3' );
		$rules[] = $this->header_css_rule( '.widgets', 'h4', 'h4' );
		$rules[] = $this->header_css_rule( '.widgets', 'h4 a', 'h4' );
		$rules[] = $this->header_css_rule( '.widgets', 'li', 'a' );
		$rules[] = $this->header_css_rule( '.actions', 'a', 'a' );
		$rules[] = $this->header_css_rule( '.post-title', 'a', 'h1' );

		$rules = array_filter( $rules );

		return implode( "\n", $rules );
	}

	/**
	 * Attempt to sniff out a new color palette based on the most recent post.
	 *
	 * @since 2.0.0
	 *
	 * @return array|WP_Error
	 */
	public function refresh() {
		$this->styles = array();

		$latest_post = get_posts( array( 'posts_per_page' => 1 ) );
		$sample_url = $latest_post ? get_permalink( $latest_post[0] ) : home_url();

		// Stylify doesn't work for HTTPS scheme, try archive.org version
		$sample_url = preg_replace( '/^https:/', 'http://web.archive.org/web/https:', $sample_url );

		$stylify_url = 'https://stylify.herokuapp.com/query?url=' . urlencode( $sample_url );

		$get = wp_remote_get( $stylify_url, array( 'timeout' => 30, 'headers' => array( 'referer' => '' ) ) );

		if ( is_wp_error( $get ) ) {
			return $get;
		}

		if ( 200 != $get['response']['code'] ) {
			return new WP_Error( 'wp_remote_get_error', wp_remote_retrieve_response_message( $get ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $get ), true );

		if ( isset( $data['error'] ) ) {
			return new WP_Error( 'stylify_error', $data['error'] );
		}

		if ( isset( $data['a-text-colour'] ) and $this->is_acceptable_text_color( $data['a-text-colour'] ) ) {
			$this->styles['a']['color'] = $data['a-text-colour'];
		}

		$this->import_stylify_typography( $data );
	}

	/**
	 * Build a CSS rule with optional selector prefix for context.
	 *
	 * @since 2.0.0
	 *
	 * @param string $context Selector prefix.
	 * @param string $selector The rule selector.
	 * @param string $source_tag Defaults to be the same as $selector.
	 * @return string
	 */
	protected function header_css_rule( $context, $selector, $source_tag = null ) {
		$source_tag = $source_tag ? $source_tag : $selector;
		$properties = $this->header_css_properties( $source_tag );
		return $properties ? $context . ' ' . $selector . ' {' . $properties . '}' : '';
	}

	/**
	 * Build properties for a CSS rule. We keep family, size, color.
	 *
	 * @since 2.0.0
	 *
	 * @param string $selector
	 * @return string
	 */
	protected function header_css_properties( $selector ) {
		$font = $this->get_value( $selector, 'font-family' );
		$properties = $font ? ' font-family: ' . $font . '; ' : '';
		$font_style = $this->get_value( $selector, 'font-style' );
		$properties .= $font_style ? ' font-style: ' . $font_style . '; ' : '';
		$font_size = $this->get_value( $selector, 'font-size' );
		$properties .= $font_size ? ' font-size: ' . $font_size . '; ' : '';
		$color = $this->get_value( $selector, 'color' );
		$color = $this->is_acceptable_text_color( $color ) ? $color : '';
		$properties .= $color ? ' color: ' . $color . '; ' : '';
		return $properties;
	}

	/**
	 * Reject white and 'ERR' as a text color.
	 *
	 * @since 2.0.0
	 *
	 * @param string $color
	 * @return bool
	 */
	protected function is_acceptable_text_color( $color ) {
		$color = strtoupper( $color );
		return ! in_array( $color, array( 'N/A', 'ERR', '#FFF', '#FFFFFF', 'WHITE', 'TRANSPARENT' ) );
	}

	/**
	 * Import site styles from stylify typography data if present.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data
	 */
	protected function import_stylify_typography( $data ) {
		if ( !isset( $data['typography'] ) ) {
			return;
		}
		foreach ( $data['typography'] as $tag => $style ) {
			$this->import_stylify_typography_style( $tag, $style );
		}
	}

	/**
	 * Import only header tags from a stylify style
	 *
	 * @since 2.0.0
	 *
	 * @param string $tag
	 * @param array $style
	 */
	protected function import_stylify_typography_style( $tag, $style ) {
		if ( $tag[0] !== 'h' and $tag !== 'body' ) {
			return;
		}
		foreach ( $style as $property => $value ) {
			$this->import_stylify_typography_property( $tag, $property, $value );
		}
	}

	/**
	 * Import a stylify property, mapping to our CSS property names.
	 *
	 * @since 2.0.0
	 *
	 * @param string $tag
	 * @param string $property
	 * @param string $value
	 */
	protected function import_stylify_typography_property( $tag, $property, $value ) {
		if ( in_array( $value, array( 'ERR', 'N/A' ) ) ) {
			return;
		}
		if ( ! isset( $this->styles[$tag] ) ) {
			$this->styles[$tag] = array();
		}
		if ( 'font' === $property ) {
			$this->styles[$tag]['font-family'] = $value;
		}
		if ( 'font-style' === $property ) {
			$this->styles[$tag]['font-style'] = $value;
		}
		if ( 'text-colour' === $property and $this->is_acceptable_text_color( $value ) ) {
			$this->styles[$tag]['color'] = $value;
		}
		if ( 'font-size' === $property ) {
			$this->styles[$tag]['font-size'] = $value;
		}
	}
}