<?php

/**
 * Options with Postmatic defaults and helpers added.
 * @since 2.0.0
 */
class Prompt_Options extends scbOptions {
	/** @var array */
	protected $overridden_options;

	/**
	 * Prompt_Options constructor.
	 * @since 2.0.0
	 * @param string $key
	 * @param string $file
	 * @param array $defaults
	 */
	public function __construct( $key = 'prompt_options', $file = null, $defaults = array() ) {

		$invite_subject = sprintf( __( 'You\'re invited to subscribe to %s', 'Postmatic' ), get_option( 'blogname' ) );

		$invite_intro = __(
			'This is an invitation to subscribe to email updates from this website. We hope it is welcome, but we promise we won\'t contact you again unless you respond.',
			'Postmatic'
		);

		$subscribed_introduction = '<h2>' . __( 'Thanks for joining the conversation!', 'Postmatic' ) . '</h2>' .
			'<p>' . __( 'We\'ll send you the latest comments as they come in.', 'Postmatic' ) . '</p>';

		$standard_defaults = array(
			'auto_subscribe_authors' => false,
			'prompt_key' => '',
			'internal_key' => '',
			'site_subscription_post_types' => array( 'post' ),
			'skip_notices' => array(),
			'service_notices' => array(),
			'whats_new_notices' => array(),
			'skip_widget_intro' => false,
			'skip_akismet_intro' => false,
			'skip_zero_spam_intro' => false,
			'skip_local_mail_intro' => false,
			'skip_moderation_user_intro' => false,
			'skip_download_intro' => true,
			'skip_labs_notice' => false,
			'redirect_to_options_page' => true,
			'send_login_info' => false,
			'email_header_type' => Prompt_Enum_Email_Header_Types::TEXT,
			'email_header_image' => 0,
			'email_header_text' => get_option( 'blogname' ),
			'email_footer_type' => Prompt_Enum_Email_Footer_Types::TEXT,
			'email_footer_text' => '',
			'email_footer_credit' => true,
			'plan' => '',
			'email_transport' => Prompt_Enum_Email_Transports::LOCAL,
			'messages' => array( 'welcome' => __( 'Welcome!', 'Postmatic' ) ),
			'invite_subject' => $invite_subject,
			'invite_introduction' => $invite_intro,
			'last_version' => 0,
			'enable_collection' => false,
			'site_icon' => 0,
			'no_post_featured_image_default' => false,
			'no_post_email_default' => false,
			'enabled_message_types' => array(),
			'excerpt_default' => false,
			'comment_opt_in_default' => false,
			'comment_opt_in_text' => __( 'Join the conversation via an occasional email', 'Postmatic' ),
			'comment_flood_control_trigger_count' => 4,
			'comment_snob_notifications' => false,
			'upgrade_required' => false,
			'enable_optins' => false,
			'enable_skimlinks' => false,
			'skimlinks_publisher_id' => '',
			'enable_webhooks' => false,
			'webhooks_urls' => array(),
			'emails_per_chunk' => 25,
			'enable_digests' => false,
			'digest_plans' => array(),
			'site_styles' => array(),
			'enable_invites' => false,
			'enable_mailchimp_import' => false,
			'enable_jetpack_import' => false,
			'enable_mailpoet_import' => false,
			'enable_post_delivery' => true,
			'enable_comment_delivery' => true,
			'subscribed_introduction' => $subscribed_introduction,
			'connection_status' => '',
			'scr_import_done' => false,
			'enable_notes' => false,
			'enable_analytics' => true,
			'account_email' => '',
			'suppress_error_submissions' => false,
			'freemius_init' => array(),
            'freemius_license_changes' => array(),
		);

		$defaults = array_merge( $standard_defaults, $defaults );
		$defaults = apply_filters( 'prompt/default_options', $defaults );

		$this->prevent_options_errors( $key );

		parent::__construct( $key, $file, $defaults );

		/**
		 * Filter overridden options.
		 *
		 * @param array $overridden_options
		 * @param array $current_options
		 */
		$filtered_options = apply_filters( 'prompt/override_options', array(), $this->get() );

		$this->overridden_options = wp_array_slice_assoc( $filtered_options, array_keys( $this->get() ) );
		if ( ! empty( $this->overridden_options ) ) {
			$this->set( $this->overridden_options );
		}

		if ( ! $this->get( 'internal_key' ) ) {
			add_action( 'plugins_loaded', array( $this, 'generate_internal_key' ) );
		}

		if ( ! $this->get( 'auto_subscribe_authors' ) and $this->enable_auto_subscribe_authors() ) {
			$this->set( 'auto_subscribe_authors', true );
		} elseif ( $this->get( 'auto_subscribe_authors' ) and ! $this->enable_auto_subscribe_authors() ) {
			$this->set( 'auto_subscribe_authors', false );
		}
	}

	/**
	 * Options that have been overridden by filtered values.
	 * @since 2.0.0
	 * @return array
	 */
	public function get_overridden_options() {
		return $this->overridden_options;
	}

	/**
	 * Shorthand to check the current email transport.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_api_transport() {
		return ( Prompt_Enum_Email_Transports::API == $this->get( 'email_transport' ) );
	}

	/**
	 * Set a random internal key.
	 *
	 * @since 2.1.0
	 */
	public function generate_internal_key() {
		$this->set( 'internal_key', wp_generate_password( 32 ) );
	}

	/**
	 * Detect and prevent options default errors.
	 *
	 * It seems that defaults may not work on Windows, and cause errors.
	 *
	 * @since 2.0.0
	 * @param string $key
	 */
	protected function prevent_options_errors( $key ) {
		if ( ! is_array( get_option( $key, array() ) ) ) {
			update_option( $key, array() );
		}
	}

	/**
	 * Whether to auto-subscribe authors to their comments.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	protected function enable_auto_subscribe_authors() {
		if ( defined( 'REPLYABLE_DISABLE_AUTO_SUBSCRIBE_AUTHORS' ) and REPLYABLE_DISABLE_AUTO_SUBSCRIBE_AUTHORS ) {
			return false;
		}

		return in_array( Prompt_Enum_Message_Types::COMMENT_MODERATION, $this->get( 'enabled_message_types' ) );
	}

}
