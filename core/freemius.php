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

		$defaults = array(
			'id' => '164',
			'slug' => 'postmatic',
			'type' => 'plugin',
			'public_key' => 'pk_3ecff09a994aaeb35de148a63756e',
			'is_live' => true,
			'is_premium' => false,
			'has_addons' => false,
			'has_paid_plans' => false,
			'menu' => array(
				'slug' => 'postmatic',
				'contact' => false,
				'account' => false,
				'support' => false,
				'parent' => array(
					'slug' => 'options-general.php',
				),
			),
		);

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
			__fs( 'hey-x' ) . ' commenting is about to get awesome arond here!<br>' .
			__(
				'Replybale lets you send beautiful, smart email notifications to your commenters. But email shouldn\'t be just one-way. Replyable let\'s you, your authors, and commenters hit reply to send a followup comment and keep the conversation going.<br />Enabling two-way email requires that our server connects to yours. Two-way plans start at $3.99 and come with a no-risk 30 day trial.<br /><strong>How would you like to use Replyable?</strong>'
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
		\Prompt_Core::$options->set( 'enable_collection', true );
		Prompt_Event_Handling::record_environment();
	}


	/**
	 * Disable our data collection on freemius deactivation.
	 * @since 2.0.0
	 */
	public static function after_account_delete() {
		\Prompt_Core::$options->set( 'enable_collection', false );
	}
}