<?php

/**
 * A post meta box for customizing email content.
 * @since 2.0.0
 */
class Prompt_Admin_HTML_Metabox extends scbPostMetabox {

	/** @var string  */
	protected static $id = 'prompt_custom_html_metabox';
	/** @var string  */
	protected static $custom_html_name = 'prompt_custom_html';
	/** @var string  */
	protected static $enable_custom_html_name = 'prompt_enable_custom_html';

	/** @var  Prompt_Post */
	protected $prompt_post;

	/**
	 * @since 2.0.0
	 * @return Prompt_Admin_HTML_Metabox
	 */
	public static function make() {
		return new Prompt_Admin_HTML_Metabox(
			self::$id,
			__( 'Postmatic Email Content', 'Postmatic' ),
			array( 'post_type' => Prompt_Core::$options->get( 'site_subscription_post_types' ) )
		);
	}

	/**
	 * @since 2.0.0
	 * @param WP_Post $post
	 */
	public static function print_publish_box_message( WP_Post $post ) {
		
		$status = html( 'span class="status ok"', __( 'Looks good', 'Postmatic' ) );
		
		$prompt_post = new Prompt_Post( $post );
		
		$content = $prompt_post->get_custom_html();
		
		$content = $content ? $content : $post->post_content;
		
		if ( self::contains_shortcodes( $content ) ) {
			$status = html( 'span class="status check"', __( 'Potential problem', 'Postmatic' ) );
		}
		
		echo html( 'div class="misc-pub-section prompt-misc-pub-content"',
			__( 'Postmatic Precheck:', 'Postmatic' ),
			' ',
			html( 'a', array( 'href' => '#' . self::$id ), $status )
		);
	}

	/**
	 * @since 2.0.0
	 * @param string $content
	 * @return bool
	 */
	protected static function contains_shortcodes( $content ) {
		return (bool) preg_match( '/\[[^\]]*\]/', $content );
	}

	/**
	 * @since 2.0.0
	 * @param string $id
	 * @param string $title
	 * @param array $args
	 */
	public function __construct( $id, $title, $args = array() ) {

		if ( isset( $_GET['post'] ) ) {
			$this->prompt_post = new Prompt_Post( intval( $_GET['post'] ) );
		}

		if ( isset( $_POST['post_ID'] ) ) {
			$this->prompt_post = new Prompt_Post( intval( $_POST['post_ID'] ) );
		}

		parent::__construct( $id, $title, $args );
	}

	/**
	 * @since 2.0.0
	 */
	public function admin_enqueue_scripts() {

		wp_enqueue_style(
			'prompt-edit-post',
			path_join( Prompt_Core::$url_path, 'css/edit-post.css' ),
			array(),
			Prompt_Core::version()
		);
		
		$script = new Prompt_Script( array(
			'handle' => 'prompt-text-metabox',
			'path' => 'js/html-metabox.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();

		$env = array(
			'custom_html_name' => self::$custom_html_name,
			'enable_custom_html_name' => self::$enable_custom_html_name,
			'confirm_prompt' => __( 
				'Note that once you have customized content for Postmatic, changes to the original post content will not be reflected in the customized version. You must maintain both independently.', 
				'postmatic-premium' 
			),
		);

		$script->localize( 'prompt_html_metabox_env', $env );
		
	}

	/**
	 * @param WP_Post $post
	 */
	public function display( $post ) {

		if ( ! $this->prompt_post ) {
			$this->prompt_post = new Prompt_Post( $post );
		}

		if ( $post->ID != $this->prompt_post->id() ) {
			return;
		}

		$sent = (bool) $this->prompt_post->sent_recipient_ids();
		$custom_html = $this->prompt_post->get_custom_html();
		$html = $custom_html ? $custom_html : $post->post_content;

		if ( $sent ) {
			echo html( 'h3', __( 'This was the version sent to subscribers.', 'Postmatic' ) );
			wp_editor( $html, 'prompt_custom_html_record' );
			return;
		}

		if ( 'publish' == $post->post_status ) {
			echo html( 'h3', __( 'This post was not sent to any subscribers.', 'Postmatic' ) );
			return;
		}

		$rendered_html = $this->get_rendered_content( $post );

		preg_match_all( '/\[[^\]]*\]/', $rendered_html, $unrendered_shortcodes );
		$unrendered_shortcodes = $unrendered_shortcodes[0];

		if ( $unrendered_shortcodes ) {
			echo html( 'h4', __( 'There are shortcodes which may not render properly in the email version. <small>You should consider replacing the following using the Customize button below:</small>', 'Postmatic' ) );
			echo html( 'p', implode( ', ', $unrendered_shortcodes ) );
		}

		preg_match_all( '/class="[^"]*incompatible[^"]*".*?href="([^"]*)"/', $rendered_html, $incompatible_urls );
		$incompatible_urls = $incompatible_urls[1];

		if ( $incompatible_urls ) {
			echo html( 'h4', sprintf(
				_n( 
					'There is one snippet of content which will automatically be replaced with an email-compatible version (usually a video or audio embed).',
					'There are %d snippets of content which will automatically be replaced with an email-compatible version (usually a video or audio embed).',
					count( $incompatible_urls ),
					'Postmatic' 
				),
				count( $incompatible_urls )
			) );
		}
		
		if ( !$unrendered_shortcodes and !$incompatible_urls ) {
			echo html( 'p',
				__( 'No content issues were detected. This will hold up nicely in email. You can still customize the email version if you like.', 'Postmatic' )
			);
		}

		$enable_custom_html = !empty( $custom_html );
		
		echo html( 
			'input type="hidden"', 
			array( 'name' => self::$enable_custom_html_name, 'value' => $enable_custom_html )
		);
		
		if ( !$enable_custom_html ) {
			echo html( 'input type="button" class="button prompt-customize-html"',
				array( 'value' => __( 'Customize Email Version', 'Postmatic' ) )
			);
		}

		echo '<div id="prompt_custom_html_editor">';
		wp_editor( $html, self::$custom_html_name );
		echo '</div>';
	}

	/**
	 * @since 2.0.0
	 * @param int $post_id
	 */
	protected function save( $post_id ) {

		if ( empty( $_POST[self::$enable_custom_html_name] ) or $post_id != $this->prompt_post->id() ) {
			return;
		}
		
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		if ( $_POST[self::$custom_html_name] == $this->prompt_post->get_custom_html() ) {
			return;
		}

		$this->prompt_post->set_custom_html( $_POST[self::$custom_html_name] );
	}

	/**
	 * @since 2.0.0
	 * @param WP_Post $post
	 * @return mixed|null|void
	 */
	protected function get_rendered_content( WP_Post $post ) {
		
		if ( empty( $post->post_content ) ) {
			return;
		}

		$context = new Prompt_Post_Rendering_Context( $post );

		$excerpt_only = Prompt_Admin_Delivery_Metabox::excerpt_only( $post->ID );

		$context->setup();
		$rendered_html = $excerpt_only ?
			apply_filters( 'the_excerpt', get_the_excerpt() ) :
			apply_filters( 'the_content', get_the_content() );
		$context->reset();

		return $rendered_html;
	}
}