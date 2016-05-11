<?php

/**
 * Manage admin notices.
 * @since 1.0.0
 */
class Prompt_Admin_Notice_Handling {
	/**
	 * @since 1.0.0
	 * @var string
	 */
	protected static $dismiss_query_param = 'postmatic_dismiss_notice';
	/**
	 * @since 1.0.0
	 * @var string
	 */
	protected static $jetpack_conflict_notice = 'jetpack_conflict';
	/**
	 * @since 1.0.0
	 * @var string
	 */
	protected static $upgrade_notice = 'upgrade';

	/**
	 * Check GET parameters for a notice dismissal.
	 *
	 * Not using for jetpack conflicts, but might as well leave the bones.
	 *
	 * @since 1.0.0
	 */
	public static function dismiss() {

		$dismissed = isset( $_GET[self::$dismiss_query_param] ) ? $_GET[self::$dismiss_query_param] : false;

		if ( ! $dismissed ) {
			return;
		}

		if ( ! in_array( $dismissed, self::valid_notices() ) ) {
			return;
		}

		$dismissed_notices = array_unique(
			array_merge( Prompt_Core::$options->get( 'skip_notices' ), array( $dismissed ) )
		);

		Prompt_Core::$options->set( 'skip_notices', $dismissed_notices );
	}

	/**
	 * Emit any notice HTML.
	 * @since 1.0.0
	 */
	public static function display() {
		self::maybe_display_jetpack_conflict();
		self::maybe_display_upgrade();
		self::maybe_display_service_notices();
		self::maybe_display_whats_new_notices();
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	protected static function valid_notices() {
		return array_merge(
			array_keys( Prompt_Core::$options->get( 'service_notices' ) ),
			array_keys( Prompt_Core::$options->get( 'whats_new_notices' ) ),
			array( self::$jetpack_conflict_notice, self::$upgrade_notice )
		);
	}

	/**
	 * @since 2.0.0
	 */
	protected static function maybe_display_whats_new_notices() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notices = Prompt_Core::$options->get( 'whats_new_notices' );
		array_walk( $notices, array( __CLASS__, 'maybe_display_notice' ) );
	}

	/**
	 * @since 2.0.0
	 */
	protected static function maybe_display_service_notices() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notices = Prompt_Core::$options->get( 'service_notices' );
		array_walk( $notices, array( __CLASS__, 'maybe_display_service_notice' ) );
	}

	/**
	 * @since 2.0.0
	 * @param string $content
	 * @param string $key
	 */
	protected static function maybe_display_service_notice( $content, $key ) {

		$postfix = sprintf(
			__( 'Changes are reflected in <a href="%s">your settings</a>.', 'Postmatic' ),
			Prompt_Core::settings_page()->url()
		);

		$content = sprintf(
			'<strong>%1$s</strong>: %2$s %3$s %4$s',
			__( 'Postmatic Service Notice', 'Postmatic' ),
			$content,
			$postfix,
			self::dismiss_link( $key )
		);

		echo scb_admin_notice( $content );
	}

	/**
	 * @since 2.0.0
	 * @param string $content
	 * @param string $key
	 * @param string $class
	 */
	protected static function maybe_display_notice( $content, $key, $class = 'updated' ) {
		
		if ( self::is_dismissed( $key ) ) {
			return;
		}
		
		$content .= self::dismiss_link( $key );

		echo scb_admin_notice( $content, $class );
	}

	/**
	 * @since 1.0.0
	 */
	protected static function maybe_display_upgrade() {

		if ( ! current_user_can( 'update_plugins' ) )
			return;

		if ( ! Prompt_Core::$options->get( 'upgrade_required' ) )
			return;

		$message = sprintf(
			__(
				'Please <a href="%s">update Postmatic</a> now to resume service. The current version is no longer supported. Thanks!',
				'Postmatic'
			),
			admin_url( 'plugins.php?plugin_status=upgrade' )
		);
		
		self::maybe_display_notice( $message, self::$upgrade_notice, 'error' );
	}

	/**
	 * @since 1.0.0
	 */
	protected static function maybe_display_jetpack_conflict() {

		if ( !class_exists( 'Jetpack' ) or !current_user_can( 'manage_options' ) )
			return;

		if ( self::is_dismissed( self::$jetpack_conflict_notice ) )
			return;

		$check_modules = array( 'subscriptions', 'comments' );

		$conflicting_modules = array_filter( $check_modules, array( 'Jetpack', 'is_module_active' ) );

		if ( ! $conflicting_modules )
			return;

		$message = sprintf(
			__(
				'Heads up: We noticed there is an active Jetpack module which is not compatible with Postmatic. You\'ll need to fix that. <a href="%s" target="_blank">Learn how to do so here</a>.',
				'Postmatic'
			),
			Prompt_Enum_Urls::JETPACK_HOWTO
		);

		$message .= self::dismiss_link( self::$jetpack_conflict_notice );

		echo scb_admin_notice( $message, 'error' );
	}

	/**
	 * @since 2.0.0
	 * @param string $key
	 * @return string
	 */
	protected static function dismiss_link( $key ) {
		return html( 'a',
			array(
				'href' => esc_url( add_query_arg( self::$dismiss_query_param, $key ) ),
				'class' => 'button postmatic-dismiss'
			),
			__( 'Dismiss' )
		);
	}

	/**
	 * @since 1.0.0
	 * @param string $notice
	 * @return bool
	 */
	protected static function is_dismissed( $notice ) {
		return in_array( $notice, Prompt_Core::$options->get( 'skip_notices' ) );
	}
}