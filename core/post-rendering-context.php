<?php

/**
 * Manage rendering a post for email.
 * @since 1.0
 */
class Prompt_Post_Rendering_Context {

	/** @var bool */
	protected $is_setup = false;
	/** @var WP_Post */
	protected $post;
	/** @var Prompt_Post */
	protected $prompt_post;
	/** @var Prompt_Site */
	protected $prompt_site;
	/** @var  array */
	protected $featured_image_src = null;
	/** @var  Prompt_Post_Rendering_Modifier[] */
	protected $modifiers;
	/** @var  Prompt_User */
	protected $author;

	/**
	 * Prompt_Post_Rendering_Context constructor.
	 * @since 1.0
	 * @param int|WP_Post $post_object_or_id
	 * @param null|array $modifiers
	 */
	public function __construct( $post_object_or_id, $modifiers = null ) {
		$this->post = get_post( $post_object_or_id );
		$this->prompt_post = new Prompt_Post( $this->post );
		$this->prompt_site = new Prompt_Site();
		$this->modifiers = $modifiers;
	}

	/**
	 * Set up the global environment needed to render a post email.
	 * @var WP_Post $post
	 * @since 1.0
	 */
	public function setup() {

		query_posts( array(
			'p' => $this->post->ID,
			'post_type' => $this->post->post_type,
			'post_status' => $this->post->post_status
		) );

		the_post();

		$this->is_setup = true;

		$this->author = new Prompt_User( $this->post->post_author );

		$this->add_modifiers();

		$this->modifiers = apply_filters(
			'prompt/post_rendering_context/modifiers',
			$this->modifiers,
			$this->post,
			$this->get_the_featured_image_src()
		);

		foreach( $this->modifiers as $modifier ) {
			$modifier->setup();
		}

	}

	/**
	 * Reset the global environment after rendering post emails.
	 * @since 1.0
	 */
	public function reset() {

		wp_reset_query();

		$this->is_setup = false;

		foreach( $this->modifiers as $modifier ) {
			$modifier->reset();
		}

	}

	/**
	 * Get Postmatic's text version of the current post content.
	 * @return mixed|string
	 */
	public function get_the_text_content() {

		$this->ensure_setup();

		$prompt_post = new Prompt_Post( $this->post );

		$text = $prompt_post->get_custom_text();

		if ( $text ) {
			return $text;
		}

		if ( Prompt_Admin_Delivery_Metabox::excerpt_only( $prompt_post->id() ) ) {
			return Prompt_Html_To_Markdown::convert( get_the_excerpt() );
		}

		$html = apply_filters( 'the_content', get_the_content() );

		$html = str_replace( ']]>', ']]&gt;', $html );

		return Prompt_Html_To_Markdown::convert( $html );
	}

	/**
	 * Get the array with the featured image url, width, and height (or false).
	 * @since 1.0
	 */
	public function get_the_featured_image_src() {

		$this->ensure_setup();

		if ( !is_null( $this->featured_image_src ) )
			return $this->featured_image_src;

		$this->featured_image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'prompt-post-featured' );

		if ( Prompt_Admin_Delivery_Metabox::suppress_featured_image( $this->post->ID ) )
			$this->featured_image_src = false;

		return $this->featured_image_src;
	}

	/**
	 * @since 2.0.0
	 * @return Prompt_User
	 */
	public function get_author() {
		$this->ensure_setup();
		return $this->author;
	}

	/**
	 * @since 2.0.0
	 * @return Prompt_Post
	 */
	public function get_post() {
		return $this->prompt_post;
	}

	/**
	 * @since 2.0.0
	 * @return Prompt_Site
	 */
	public function get_site() {
		return $this->prompt_site;
	}

	/**
	 * @since 1.0
	 * @return string Menu HTML.
	 */
	public function alternate_versions_menu() {
		global $polylang;

		$wpml_selector = $this->wpml_language_selector_html();

		if ( $wpml_selector ) {
			return $wpml_selector;
		}

		if ( ! class_exists( 'PLL_Switcher' ) )
			return '';

		$switcher = new PLL_Switcher();

		$languages = $switcher->the_languages(
			$polylang->links,
			array(
				'post_id' => $this->post->ID,
				'echo' => false,
				'hide_if_no_translation' => true,
				'hide_current' => true,
			)
		);

		return empty( $languages ) ? '' : html( 'ul class="alternate-languages"', $languages );
	}

	/**
	 * @since 1.0
	 * @return bool whether the post has content that would be stripped by strip_fancy_content()
	 */
	public function has_fancy_content() {

		if ( stripos( $this->post->post_content, '<img' ) !== false )
			return true;

		if ( stripos( $this->post->post_content, '<iframe'  ) !== false )
			return true;

		if ( stripos( $this->post->post_content, '<object' ) !== false )
			return true;

		$sans_shortcodes = strip_shortcodes( $this->post->post_content );

		return ( $sans_shortcodes != $this->post->post_content );
	}

	/**
	 * @since 1.0
	 */
	protected function ensure_setup() {

		if ( ! $this->is_setup ) {
			$this->setup();
			return;
		}

		if ( get_the_ID() != $this->post->ID ) {
			// A widget or something has messed up the global query - redo it
			$this->setup();
		}

	}

	/**
	 * @since 1.4.0
	 */
	protected function add_modifiers() {

		if ( ! is_null( $this->modifiers ) ) {
			return;
		}

		$this->modifiers = array(
			new Prompt_Custom_HTML_Post_Rendering_Modifier(),
			new Prompt_Handlebars_Escape_Post_Rendering_Modifier()
		);

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) ) {
			$this->modifiers[] = new Prompt_Local_Post_Rendering_Modifier();
		}
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function wpml_language_selector_html() {
		ob_start();
		do_action( 'wp_footer_language_selector' );
		return ob_get_clean();
	}
}