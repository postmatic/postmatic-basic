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

		self::$freemius = fs_dynamic_init( array(
			'id' => '164',
			'slug' => 'postmatic',
			'public_key' => 'pk_3ecff09a994aaeb35de148a63756e',
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
			__fs( 'hey-x' ) . ' just a quick request:<br>' .
			__(
				'Help us improve Postmatic by sharing usage statistics with us? It will help with shaping our roadmap as well as offering you support - <strong>plus we\'ll send you a coupon for 10%% off your first 6 months of paid Postmatic service.</strong><br /><br /> Feel free to skip. Postmatic will still be fully functional.',
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