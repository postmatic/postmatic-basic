<?php

/**
 * Freemius integration
 * @since 2.0.0
 */
class Prompt_Freemius {

	/**
	 * @since 2.0.0
	 * @var Freemius
	 */
	protected static $freemius;

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	public static function is_loaded() {
		return isset( self::$freemius );
	}

	/**
	 * @since 2.0.0
	 * @return null
	 */
	public static function load() {

		if ( self::is_loaded() ) {
			return null;
		}

		require_once Prompt_Core::$dir_path . '/vendor/freemius/wordpress-sdk/start.php';

		$defaults = Prompt_Core::$options->get( 'freemius_init' );

		$init_data = defined( 'POSTMATIC_FREEMIUS_INIT' ) ? unserialize( POSTMATIC_FREEMIUS_INIT ) : array();

		self::$freemius = fs_dynamic_init( array_replace_recursive( $defaults, $init_data ) );

		self::$freemius->override_i18n( array(
			'opt-in-connect' => __( 'Two-way conversations', 'Postmatic' ),
			'skip' => __( 'One-way notifications', 'Postmatic' ),
		) );

		self::$freemius->add_filter( 
			'connect_message_on_update', 
			array( __CLASS__, 'custom_connect_message_on_update' ), 
			10, 
			6
		);
		
		self::$freemius->add_action(
			'after_account_connection',
			array( __CLASS__, 'after_account_connection' ),
			10,
			2
		);
		
		self::$freemius->add_action( 'after_account_delete', array( __CLASS__, 'after_account_delete' ) );

		self::$freemius->add_action( 'after_license_change', array( __CLASS__, 'after_license_change' ) );

		self::$freemius->add_filter( 'sticky_message_trial_started', array( __CLASS__, 'sticky_message_trial_started' ) );
	}

	/**
	 * Use our own connect message.
	 * @since 2.0.0
	 * @param $message
	 * @param $user_first_name
	 * @param $plugin_title
	 * @param $user_login
	 * @param $site_link
	 * @param $freemius_link
	 * @return string
	 */
	public static function custom_connect_message_on_update(
		$message,
		$user_first_name,
		$plugin_title,
		$user_login,
		$site_link,
		$freemius_link
	) {
		return sprintf(
			__fs( 'hey-x' ) . ' commenting is about to get awesome around here!<br>' .
			__(
				'Replyable lets you send beautiful, smart email notifications to your commenters. But email shouldn\'t be just one-way. Replyable let\'s you, your authors, and commenters hit reply to send a followup comment and keep the conversation going.<br />Enabling two-way email requires that our server connects to yours. Two-way plans start at $2.99 and come with a no-risk 30 day trial.<br /><strong>How would you like to use Replyable?</strong>'
				,
				'Postmatic'
			),
			$user_first_name,
			'<b>' . $plugin_title . '</b>',
			'<b>' . $user_login . '</b>',
			$site_link,
			$freemius_link
		);
	}

	/**
	 * Enable our data collection on freemius activation.
	 * @since 2.0.0
	 * @param FS_User $user
	 * @param FS_Site $site
	 */
	public static function after_account_connection( $user, $site ) {
		Prompt_Core::$options->set( 'enable_collection', true );
		Prompt_Event_Handling::record_environment();
	}


	/**
	 * Disable our data collection on freemius deactivation.
	 * @since 2.0.0
	 */
	public static function after_account_delete() {
		Prompt_Core::$options->set( 'enable_collection', false );
	}

	/**
	 * Keep track of whether premium service is enabled.
	 * @since 2.1.0
	 * @param string $event
	 */
	public static function after_license_change( $event ) {
		$init_values = Prompt_Core::$options->get( 'freemius_init' );
		if ( in_array( $event, array( 'cancelled', 'expired', 'trial_expired' ) ) ) {
			$init_values['is_premium'] = false;
		} else {
			$init_values['is_premium'] = true;
		}
		Prompt_Core::$options->set( 'freemius_init', $init_values );
	}

	/**
	 * Customize the trial started sticky message.
	 *
	 * @since 2.1.0
	 * @param string $message
	 * @return string
	 */
	public static function sticky_message_trial_started( $message ) {
		return __( 'Great, your trial has started!', 'Postmatic' );
	}
}