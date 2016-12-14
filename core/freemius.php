<?php

/**
 * Freemius integration
 * @since 2.0.0
 * @since 2.1.0 Made instantiable.
 */
class Prompt_Freemius implements Prompt_Interface_License_Status {

	/**
	 * @since 2.0.0
	 * @var Freemius
	 */
	protected $freemius;

	/**
	 * @since 2.1.0
	 * @var Prompt_Options
	 */
	protected $options;

	/**
	 * Instantiate Freemius integration.
	 * @since 2.1.0
	 * @param Prompt_Options $options Plugin options.
	 */
	public function __construct( Prompt_Options $options) {
		$this->options = $options;
		$this->freemius = null;
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_loaded() {
		return ! is_null( $this->freemius );
	}

	/**
	 * @since 2.0.0
	 */
	public function load() {

		if ( $this->is_loaded() ) {
			return null;
		}

		require_once Prompt_Core::$dir_path . '/vendor/freemius/wordpress-sdk/start.php';

		$defaults = $this->options->get( 'freemius_init' );

		$init_data = defined( 'POSTMATIC_FREEMIUS_INIT' ) ? unserialize( POSTMATIC_FREEMIUS_INIT ) : array();

		$this->freemius = fs_dynamic_init( array_replace_recursive( $defaults, $init_data ) );

		$this->freemius->override_i18n( array(
			'opt-in-connect' => __( 'Two-way conversations', 'Postmatic' ),
			'skip' => __( 'One-way notifications', 'Postmatic' ),
		) );

		$this->freemius->add_filter(
			'connect_message',
			array( $this, 'custom_connect_message' ),
			10,
			6
		);

		$this->freemius->add_filter(
			'connect_message_on_update', 
			array( $this, 'custom_connect_message' ),
			10, 
			6
		);
		
		$this->freemius->add_action(
			'after_account_connection',
			array( $this, 'after_account_connection' ),
			10,
			2
		);
		
		$this->freemius->add_action( 'after_account_delete', array( $this, 'after_account_delete' ) );

		$this->freemius->add_action( 'after_license_change', array( $this, 'after_license_change' ) );

		$this->freemius->add_filter( 'sticky_message_trial_started', array( $this, 'sticky_message_trial_started' ) );
	}

	/**
	 * Use our own connect message.
	 *
	 * @since 2.0.0
	 * @param string $message
	 * @param string $user_first_name
	 * @param string $plugin_title
	 * @param string $user_login
	 * @param string $site_link
	 * @param string $freemius_link
	 * @return string
	 */
	public function custom_connect_message(
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
	public function after_account_connection( $user, $site ) {
		$this->options->set( 'enable_collection', true );
		Prompt_Event_Handling::record_environment();
	}


	/**
	 * Disable our data collection on freemius deactivation.
	 * @since 2.0.0
	 */
	public function after_account_delete() {
		$this->options->set( 'enable_collection', false );
		$init_values = $this->options->get( 'freemius_init' );
		$init_values['is_premium'] = false;
		$this->options->set( 'freemius_init', $init_values );
	}

	/**
	 * Keep track of whether premium service is enabled.
	 * @since 2.1.0
	 * @param string $event
	 */
	public function after_license_change( $event ) {
		$init_values = $this->options->get( 'freemius_init' );
		if ( in_array( $event, array( 'cancelled', 'expired', 'trial_expired' ) ) ) {
			$init_values['is_premium'] = false;
		} else {
			$init_values['is_premium'] = true;
		}
		$this->options->set( 'freemius_init', $init_values );
	}

	/**
	 * Customize the trial started sticky message.
	 *
	 * @since 2.1.0
	 * @param string $message
	 * @return string
	 */
	public function sticky_message_trial_started( $message ) {
		return __( 'Great, your trial has started!', 'Postmatic' );
	}

	/**
	 * Whether a premium license is in effect.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	public function is_premium() {
		return $this->freemius->is_premium();
	}

	/**
	 * Whether a trial is available to start.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	public function is_trial_available() {
		return ! $this->freemius->is_trial_utilized();
	}

	/**
	 * Whether a trial has started but not expired.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	public function is_trial_underway() {
		return $this->freemius->is_trial();
	}
}