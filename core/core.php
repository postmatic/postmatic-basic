<?php

/**
 * Static central routing class.
 * @since 0.1.0
 */
class Prompt_Core {
	const SUPPORT_EMAIL = 'support@gopostmatic.com';
	const ABUSE_EMAIL = 'abuse@gopostmatic.com';

	/** @var string */
	static public $dir_path;
	/** @var string */
	static public $basename;
	/** @var string */
	static public $url_path;
	/** @var Prompt_Options */
	static public $options;

	/**
	 * @since 1.0.0
	 * @var string
	 */
	static protected $version = '';
	/**
	 * @since 1.0.0
	 * @var string
	 */
	static protected $full_version = '';

	/**
	 * @since 2.0.0
	 * @var Prompt_Admin_Options_Page
	 */
	static protected $settings_page = null;

	/**
	 * @since 2.1.0
	 * @var Prompt_Freemius
	 */
	static protected $freemius = null;

	/**
	 * @since 2.0.0
	 */
	public static function load() {
		self::$dir_path = dirname( dirname( __FILE__ ) );
		self::$basename = plugin_basename( self::$dir_path . '/postmatic.php' );
		self::$url_path = plugins_url( '', dirname( __FILE__ ) );

		load_plugin_textdomain( 'Postmatic', '', path_join( dirname( self::$basename ), 'lang' ) );

		scb_init();

        add_filter( 'prompt/default_options', array( 'Postmatic\Commentium\Filters\Options', 'default_options' ) );
		self::$options = new Prompt_Options();

		add_action( 'plugins_loaded', array( __CLASS__, 'action_plugins_loaded' ) );

		if ( !class_exists( 'Postmatic\Premium\Core' ) and !self::unit_testing() ) {
			self::$freemius = Prompt_Root::load_freemius();
		}
	}

	/**
	 * Continue loading with dependencies.
	 */
	public static function action_plugins_loaded() {

		self::add_hooks();

		if ( is_admin() ) {
			self::settings_page();
		}

		/**
		 * Fires when Postmatic has loaded.
		 *
		 * This happens after plugins are loaded {@see 'plugins_loaded'}, and always fires when Postmatic is active.
		 *
		 * @since 1.0.0
		 */
		do_action( 'prompt/core_loaded' );
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	protected static function unit_testing() {
		// Stinky, but need to prevent loading freemius while unit testing
		return function_exists( '_manually_load_plugin' );
	}
	
	/**
	 * Register the WordPress hooks we will respond to.
	 */
	protected static function add_hooks() {

		if ( defined( 'PROMPT_NO_OUTBOUND_EMAILS' ) and PROMPT_NO_OUTBOUND_EMAILS ) {
			add_filter( 'prompt/outbound/emails', '__return_empty_array' );
			add_action(
				'prompt/outbound/batch',
				create_function( '$b', '$b->set_individual_message_values( array() );' )
			);
		}

		register_deactivation_hook( self::$basename, array( 'Prompt_Event_Handling', 'record_deactivation' ) );
		register_activation_hook( self::$basename, array( 'Prompt_Event_Handling', 'record_reactivation' ) );

		register_activation_hook( self::$basename, array( 'Prompt_Site_Icon', 'ensure' ) );

		add_action( 'admin_init', array( __CLASS__, 'detect_version_change' ) );

		add_action( 'template_redirect', array( 'Prompt_Routing', 'template_redirect' ) );
		add_filter( 'query_vars', array( 'Prompt_Routing', 'add_query_vars' ) );

		add_action( 'rest_api_init', array( 'Prompt_Rest_Api', 'init' ) );

		add_action( 'wp_ajax_nopriv_prompt/pull-updates', array( 'Prompt_Web_Api_Handling', 'receive_pull_updates' ) );
		add_action( 'wp_ajax_nopriv_prompt/pull-configuration', array( 'Prompt_Web_Api_Handling', 'receive_pull_configuration' ) );
		add_action( 'wp_ajax_nopriv_prompt/instant-callback', array( 'Prompt_Web_Api_Handling', 'receive_callback' ) );
		add_action( 'wp_ajax_nopriv_prompt/scheduled-callback', array( 'Prompt_Web_Api_Handling', 'receive_callback' ) );
		add_action( 'wp_ajax_nopriv_prompt/ping', array( 'Prompt_Web_Api_Handling', 'receive_ping' ) );
		add_action( 'wp_ajax_nopriv_prompt/key', array( 'Prompt_Web_Api_Handling', 'receive_key' ) );

		add_action( 'wp_insert_comment', array( 'Prompt_Outbound_Handling', 'action_wp_insert_comment' ), 10, 2 );
		add_action( 'transition_comment_status', array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ), 10, 3 );
		add_filter( 'comment_notification_recipients', array( 'Prompt_Outbound_Handling', 'filter_comment_notification_recipients' ) );

		add_action( 'wp_insert_comment', array( 'Postmatic\Commentium\Actions\Comment_IQ', 'maybe_save_comment' ) );
		add_action( 'edit_comment', array( 'Postmatic\Commentium\Actions\Comment_IQ', 'maybe_save_comment' ) );
		add_action( 'save_post', array( 'Postmatic\Commentium\Actions\Comment_IQ', 'maybe_save_post_article' ), 10, 2 );

		add_action( 'comment_approved_to_unapproved', array( 'Postmatic\Commentium\Filters\Comment_Moderation', 'approved_to_unapproved' ) );
		add_filter( 'comment_moderation_recipients', array( 'Postmatic\Commentium\Filters\Comment_Moderation', 'recipients' ), 10, 2 );

        add_filter( 'prompt/make_comment_flood_controller', array( 'Postmatic\Commentium\Filters\Factory', 'make_comment_flood_controller' ), 10, 2 );
		add_filter( 'prompt/comment_notifications/allow', array( 'Postmatic\Commentium\Filters\Comment_Notifications', 'allow' ), 10, 2 );
        add_filter( 'replyable/comment_form/opt_in_tooltip_text', array( 'Postmatic\Commentium\Filters\Comment_Form_Handling', 'tooltip' ) );
        add_filter( 'prompt/options_page/tabs', array( 'Postmatic\Commentium\Filters\Options_Page', 'tabs' ) );

		add_action( 'prompt/mailing/send', array( 'Prompt_Mailing', 'send' ) );
		add_action( 'prompt/comment_mailing/send_notifications', array( 'Prompt_Comment_Mailing', 'send_notifications' ) );
		add_action( 'prompt/subscription_mailing/send_agreements', array( 'Prompt_Subscription_Mailing', 'send_agreements' ), 10, 4 );
		add_action( 'prompt/subscription_mailing/send_cached_invites', array( 'Prompt_Subscription_Mailing', 'send_cached_invites' ) );
		add_action( 'prompt/inbound_handling/pull_updates', array( 'Prompt_Inbound_Handling', 'pull_updates' ) );
		add_action( 'prompt/inbound_handling/acknowledge_updates', array( 'Prompt_Inbound_Handling', 'acknowledge_updates' ) );
		add_action( 'prompt/configuration_handling/pull_configuration', array( 'Prompt_Configuration_Handling', 'pull_configuration' ) );
		add_action( 'prompt/cron_handling/clear_all', array( 'Prompt_Cron_Handling', 'clear_all' ) );

		add_action( 'wp_ajax_prompt_comment_unsubscribe',             array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_comment_unsubscribe' ) );
		add_action( 'wp_ajax_nopriv_prompt_comment_unsubscribe',      array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_comment_unsubscribe' ) );
		add_action( 'wp_ajax_prompt_is_connected',                    array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_is_connected' ) );
		add_action( 'wp_ajax_prompt_dismiss_notice',                  array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_dismiss_notice' ) );

		add_action( 'delete_user',              array( 'Prompt_User_Handling', 'delete_subscriptions' ) );
		add_action( 'edit_user_profile',        array( 'Prompt_User_Handling', 'render_profile_options' ) );
		add_action( 'show_user_profile',        array( 'Prompt_User_Handling', 'render_profile_options' ) );

		add_action( 'edit_user_profile_update', array( 'Prompt_User_Handling', 'update_profile_options' ) );
		add_action( 'personal_options_update', array( 'Prompt_User_Handling', 'update_profile_options' ) );
		add_filter( 'allow_password_reset', array( 'Prompt_User_Handling', 'filter_allow_password_reset' ), 10, 2 );

		add_action( 'comment_form', array( 'Prompt_Comment_Form_Handling', 'form_content' ) );
		add_action( 'comment_post', array( 'Prompt_Comment_Form_Handling', 'handle_form' ), 10, 2 );
		add_action( 'comment_form_after', array( 'Prompt_Comment_Form_Handling', 'after_form' ) );
		add_filter( 'epoch_iframe_scripts', array( 'Prompt_Comment_Form_Handling', 'enqueue_epoch_assets' ) );

		add_filter( 'manage_users_columns', array( 'Prompt_Admin_Users_Handling', 'manage_users_columns' ) );
		add_filter( 'manage_users_custom_column', array( 'Prompt_Admin_Users_Handling', 'subscriptions_column' ), 10, 3 );

		add_action( 'admin_init', array( 'Prompt_Admin_Notice_Handling', 'dismiss' ) );
		add_action( 'admin_notices', array( 'Prompt_Admin_Notice_Handling', 'display' ) );

		/**
		 * Fires after Postmatic has added its main hooks.
		 *
		 * This only happens when a key has been entered.
		 *
		 * @since 1.4.11
		 */
		do_action( 'prompt/hooks_added' );
	}

	/**
	 * @since 1.0.0
	 */
	public static function detect_version_change() {

		if ( self::version() == self::$options->get( 'last_version' ) ) {
			return;
		}

		self::$options->set( 'last_version', self::version() );
		self::$options->set( 'upgrade_required', false );
		self::$options->set( 'skip_download_intro', false );
		self::$options->set( 'whats_new_notices', array() );

		if ( self::$options->get( 'enable_collection' ) ) {
			Prompt_Event_Handling::record_environment();
		}
	}

	/**
	 * Get the plugin version.
	 *
	 * @param bool $full If true, append build or commit. Default false.
	 * @return string
	 */
	public static function version( $full = false ) {
		if ( $full and self::$full_version )
			return self::$full_version;

		if ( !$full and self::$version )
			return self::$version;

		$build_file = path_join( self::$dir_path, 'version' );

		if ( file_exists( $build_file ) ) {
			self::$full_version = file_get_contents( $build_file );
			$parts = explode( '-', self::$full_version );
			self::$version = $parts[0];
			return $full ? self::$full_version : self::$version;
		}

		// This is not a built package, dig around some more

		if ( !function_exists( 'get_plugin_data' ) )
			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		$plugin_data = get_plugin_data( self::$dir_path . '/postmatic.php' );
		self::$version = $plugin_data['Version'];

		if ( !$full )
			return self::$version;

		if ( getenv( 'CI' ) )
			return self::$version . '-' . getenv( 'CI_COMMIT_ID' );

		$head = path_join( self::$dir_path, '.git/HEAD' );

		if ( !file_exists( $head ) )
			return self::$version;

		$ref = path_join( self::$dir_path, '.git/' . trim( substr( file_get_contents( $head ), 5 ) ) );

		if ( !file_exists( $ref ) )
			return self::$version;

		self::$full_version = trim( self::$version . '-' . file_get_contents( $ref ) );

		return self::$full_version;
	}

	/**
	 * @return Prompt_Admin_Options_Page
	 */
	public static function settings_page() {
		if ( !self::$settings_page )
			self::$settings_page = new Prompt_Admin_Options_Page(
				self::$dir_path . '/postmatic.php',
				self::$options,
				self::$freemius,
				self::$options->get_overridden_options()
			);

		return self::$settings_page;
	}

	/**
	 * Whether the site is using the API email transport.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public static function is_api_transport() {
		return ( Prompt_Enum_Email_Transports::API == self::$options->get( 'email_transport' ) );
	}

} // end Prompt_Core class
