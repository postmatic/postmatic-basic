<?php
 

class Prompt_Optins extends Prompt_Core {

	/**
	 * Optin related options
	 *
	 * @var array
	 */
	private static $optins_options;


	/**
	 * Load up the optins if needed
	 */
	public static function maybe_load() {

		if ( parent::$options->get( 'enable_optins' ) ) {
			self::add_hooks( );
		}

	}

	/**
	 * Add hooks for the optins we are using now.
	 *
	 * @param string|array $type Type or types of optins.
	 */
	static function add_hooks() {
		$options = self::optins_options();
 		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 100 );
		add_action( 'wp_footer', array( __CLASS__, 'footer_markup' ) );
		if ( $options[ 'optins_topbar_enable' ] ) {
			add_action( 'wp_head', array( __CLASS__, 'topbar_markup' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'topbar_assets' ) );
		}

		if ( $options[ 'optins_inpost_enable' ] ) {
			add_action( 'wp_head', array( __CLASS__, 'maybe_inpost' ) );
		}

		$types = self::determine_type();
		if ( ! empty( $types ) ) {
			add_action( 'wp_head', array( __CLASS__, 'print_css' ) );
		}

	}

	/**
	 * Determine the type of the current optin(s) needed.
	 *
	 * @return array Array of current in use types in form of type => theme
	 */
	static function determine_type() {
		$types = array_keys( Prompt_Optins::types() );
		$themes = array_keys( Prompt_Optins::themes() );
		$options = self::optins_options();
		$current_types = array();
		foreach( $types as $type ) {
			$field = "optins_{$type}_enable";
			if ( $options[ $field ] ) {
				$theme_key = "optins_{$type}_theme";
				$theme = $options[ $theme_key ];
				$current_types[ $type ] = $theme;
			}

		}

		return $current_types;

	}

	/**
	 * Get optin options
	 *
	 * @return array
	 */
	public static function optins_options() {
		$saved = get_option( 'prompt_options' );
		foreach ( self::options_fields() as $field => $default ) {
			if ( isset( $saved[ $field ] ) ) {
				self::$optins_options[ $field ] = $saved[ $field ];
			}else{
				self::$optins_options[ $field ] = $default;
			}
		}

		return self::$optins_options;

	}

	/**
	 * Load script/styles
	 *
	 * @uses wp_enqueue_scripts
	 */
	public static function assets() {
		$path = self::$url_path . '/vendor/calderawp/caldera-modals/';
		wp_enqueue_style( 'caldera-modals', $path . 'modals.css' );
		wp_enqueue_script( 'caldera-modals', $path . 'caldera-modals.js', array( 'jquery' ), false, true );
		wp_localize_script( 'caldera-modals', 'postmatic_optin_options', self::data_to_localize() );
	}

	/**
	 * Format data for wp_localize_script
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected static function data_to_localize() {
		$options = self::optins_options();
		$data = array();
		if ( $options[ 'optins_popup_enable' ] ) {

			if ( 'timed' == $options[ 'optins_popup_type' ] ) {
				$trigger = (int) $options[ 'optins_popup_time' ];
				$trigger = $trigger * 1000;
			}else{
				$trigger = $options[ 'optins_popup_type' ];
			}

			$data[] = array(
				'type' => 'popup',
				'trigger' => $trigger,
				'title' => $options[ 'optins_popup_title' ],
				'admin_test' => $options[ 'optins_popup_admin_test'] && current_user_can( 'manage_options' ),
			);

		}

		if ( $options[ 'optins_bottom_enable' ] ) {

			if ( 'timed' == $options[ 'optins_bottom_type' ] ) {
				$trigger = (int) $options[ 'optins_bottom_time' ];
				$trigger = $trigger * 1000;
			}else{
				$trigger = $options[ 'optins_bottom_type' ];
			}

			$data[] = array(
				'type' => 'bottom',
				'trigger' => $trigger,
				'title' => $options[ 'optins_bottom_title' ]
			);

		}

		return $data;

	}




	/**
	 * Echo form form for modal/topbar/etc
	 *
	 * @param string|bool $id Optional. ID attribute for HTML elements. IF false, created based on $type.
	 * @param string $type. Type of optin being used.
	 * @param bool $echo Optional. Whether to echo, the default, or return.
	 * @param bool $hide Optional. Whether to hide initially. Default is true, which applies "display:none;"
	 *
	 * @return string in some contexts, prints in others.
	 */
	public static function the_modal( $id = "postmatic-optin-widget", $type, $echo = true, $hide = true ) {
		if ( ! $id ) {
			$id = "postmatic-{$type}-optin-widget";
		}

		$id = "postmatic-{$type}-optin-widget";


		if ( class_exists( 'Prompt_Subscribe_Widget_Shortcode' ) ) {
			$title_desc = self::get_widget_title_desc( $type );

			$attributes = array(
				'title' => $title_desc[ 'title' ],
				'subscribe_prompt' => $title_desc[ 'desc' ],
				'collect_name' => true,
				'template_path' => null,
			);

			if ( 'bottom' == $type ) {
				$attributes[ 'title' ] = '';
			}

			ob_start();

			the_widget( 'Prompt_Subscribe_Widget', $attributes );

			$widget =  ob_get_clean();
			$search = 'widget widget_prompt_subscribe_widget';
			$widget = str_replace( $search, 'widget_prompt_subscribe_widget', $widget );

			if ( $hide ) {
				$style = 'style="display: none;"';
			}else{
				$style = '';
			}

			$pattern = '<div id="%1s" class="postmatic-optin-widget" %2s >%3s</div>';

			if ( $echo ) {
				printf( $pattern, esc_attr( $id ), $style, $widget );
			} else {
				return sprintf( $pattern, esc_attr( $id ), $style, $widget );
			}

		}

	}

	/**
	 * The types of modals we can have
	 *
	 * @return array
	 */
	public static function types() {
		return array(
			'popup' => __( 'Popup', 'Postmatic' ),
			'bottom' => __( 'Bottom of Page', 'Postmatic' ),
			'topbar' => __( 'Topbar', 'Postmatic' ),
			'inpost' => __( 'Bottom of Post', 'Postmatic')
		);
	}

	/**
	 * Check if a type of optin is valid
	 *
	 * @access protected
	 *
	 * @param string $type Type of optin
	 *
	 * @return bool
	 */
	protected static function is_valid_type( $type ) {
		if ( array_key_exists( $type, self::types() ) ) {
			return true;
		}

	}

	/**
	 * Possible trigger for bottom/popups
	 *
	 * @return array
	 */
	public static function popup_bottom_trigger_options() {
		$triggers = array(
			'comment' => __( 'After a user submits a comment', 'Postmatic' ),
			'timed' => __( 'After a set period of time.', 'Postmatic' ),
			'bottom' => __( 'After the user scrolls to the bottom of the post or page.', 'Postmatic' ),
		);

		if ( ! defined( 'EPOCH_VER' ) ) {
			unset( $triggers[ 'comment'] );
		}

		return $triggers;
		
	}

	/**
	 * Option fields for optins
	 *
	 * @return array
	 */
	public static  function options_fields() {
		$fields = array(
			'optins_popup_enable' => false,
			'optins_popup_type' => 'timed',
			'optins_popup_time' => 5000,
			'optins_popup_theme' => 'light',
			'optins_popup_title' => '',
			'optins_popup_desc' => '',
			'optins_popup_admin_test' => false,
			'optins_popup_image' => 0,
			'optins_bottom_enable' => false,
			'optins_bottom_type' => 'timed',
			'optins_bottom_time' => 5000,
			'optins_bottom_theme' => 'light',
			'optins_bottom_title' => '',
			'optins_bottom_desc' => '',
			'optins_bottom_image' => 0,
			'optins_topbar_enable' => false,
			'optins_topbar_theme' => 'light',
			'optins_topbar_title' => '',
			'optins_topbar_desc' => '',
			'optins_inpost_enable' => false,
			'optins_inpost_ids' => 'all',
			'optins_inpost_theme' => 'light',
			'optins_inpost_title' => '',
			'optins_inpost_desc' => '',
			'optins_inpost_image' => 0,
		);

		return $fields;

	}

	/**
	 * Create markup for the topbar
	 *
	 * @uses wp_head
	 *
	 * @return string
	 */
	public static function topbar_markup() {
	?>
		<div id="postmatic-optin-topbar-wrap">
			<div id="postmatic-optin-topbar">
					<?php
					  self::the_modal( 'postmatic-topbar-subscribe-form', 'topbar', true, false );
					?>
				<close>&nbsp;</close>
				<open>&nbsp;</open>
			</div>
		</div>
		<?php

	}

	/**
	 * Add script/style for the topbar
	 *
	 * @uses wp_enqueue_scripts
	 */
	public static function topbar_assets() {
		wp_enqueue_script( 'postmatic-topbar', self::$url_path .'/js/topbar.js', array( 'jquery' ) );
	}

	/**
	 * Maybe hook the content to add an optin to the bottom of a post
	 *
	 */
	public static function maybe_inpost() {
		if ( self::should_add_inpost() ) {
			add_filter( 'the_content', array( __CLASS__, 'content_filter' ) );
		}
	}

	/**
	 * Whether an optin should be added to content for the current query
	 *
	 * @return bool
	 */
	protected static function should_add_inpost() {
		$enable_post_types = apply_filters( 'prompt/optins/inpost_post_types', array( 'post' ) );

		if ( ! is_singular( $enable_post_types ) ) {
			return false;
		}

		$options = self::optins_options();

		if ( 'all' == $options['optins_inpost_ids'] ) {
			return true;
		}

		if ( ! is_array( $options['optins_inpost_ids'] ) ) {
			return false;
		}

		if ( in_array( get_the_ID(), $options['optins_inpost_ids'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add the optin to bottom of post
	 *
	 * @uses the_content
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function content_filter( $content ) {
		$content .= self::the_modal( false, 'inpost', false, false );
		return $content;
	}

	/**
	 * Possible themes for options
	 *
	 * @return array
	 */
	public static function themes() {
		$themes = Prompt_Optins_Style::themes();
		$themes = array_combine( array_keys( $themes ), wp_list_pluck( $themes, 'label' ) );
		return $themes;
	}

	/**
	 * Print CSS for optins
	 *
	 * @uses wp_head
	 */
	public static function print_css() {
		$out = '';
		$types = self::determine_type();
		foreach ( $types as $type => $theme ) {
			$out .= Prompt_Optins_Style::make_css( $type, $theme, self::get_custom_image_url( $type ) );
		}

		if ( $out ) {
			echo '<style type="text/css">' . $out . '</style>';
		}

	}

	/**
	 * Get the custom image URL for a type if set
	 *
	 * @param string $type
	 * @return string Empty string if not set
	 */
	protected static function get_custom_image_url( $type ) {
		$option_name = 'optins_' . $type . '_image';
		$options = self::optins_options();
		if ( empty( $options[$option_name] ) ) {
			return '';
		}
		$image = new Prompt_Attachment_Image( $options[$option_name] );
		return $image->url();
	}

	/**
	 * Get widget title/desc
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	protected static function get_widget_title_desc( $type ) {
		$options = self::optins_options();
		$title = "optins_{$type}_title";
		$desc = "optins_{$type}_desc";
		return array(
			'title' => $options[ $title ],
			'desc' => $options[ $desc ]
		);
	}

	/**
	 * Output bottom/popup modal content in footer.
	 *
	 * @uses wp_footer
	 */
	public static function footer_markup() {
		$types = self::determine_type();
		foreach ( $types as $type => $theme ) {
			if ( in_array( $type , array(
				'popup',
				'bottom'
			) ) ) {
				self::the_modal( false, $type, true);
			}

		}
	}
}
