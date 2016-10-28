<?php

/**
 * Handle GET requests for Postmatic routes, e.g. unsubscribes.
 *
 * @since 2.1.0
 */
class Prompt_Routing {

	protected static $route_query_var = 'postmatic_route';

	/**
	 * Add the Postmatic route query variable.
	 *
	 * @since 2.1.0
	 * @param array $query_vars
	 * @return array
	 */
	public static function add_query_vars( $query_vars ) {
		$query_vars[] = self::$route_query_var;
		return $query_vars;
	}

	/**
	 * Route requests including the routing query variable.
	 *
	 * @since 2.1.0
	 * @param bool $exit Whether to exit after routing
	 */
	public static function template_redirect( $exit = true ) {
		$route = get_query_var( self::$route_query_var );

		if ( ! $route ) {
			return;
		}

		if ( ! method_exists( __CLASS__, $route ) ) {
			return;
		}

		self::$route( array_map( 'wp_unslash', $_GET ) );

		if ( $exit ) {
			exit();
		}
	}

	/**
	 * Get an unsubscribe URL for one or all lists.
	 *
	 * @since 2.1.0
	 * @param int $user_id
	 * @param string $list_slug Leave empty for all lists.
	 * @return string
	 */
	public static function unsubscribe_url( $user_id, $list_slug = '' ) {
		$args = array( 'u' => $user_id );

		if ( $list_slug ) {
			$args['l'] = $list_slug;
		}

		return self::signer()->sign_url( home_url(), $args );
	}

	/**
	 * Handle an unsubscribe request.
	 *
	 * @since 2.1.0
	 * @param array $args
	 */
	protected static function unsubscribe( $args ) {

		$view = new Prompt_Template( 'transaction-view.php' );
		$title = __( 'Unsubscribe', 'Postmatic' );

		$user = isset( $args['email'] ) ? get_user_by( 'email', sanitize_email( $args['email'] ) ) : null;

		if ( ! self::signer()->is_valid( $args ) or ! $user ) {
			$status = __(
				'We tried to unsubscribe you, but there was some required information missing from this request.',
				'Postmatic'
			);
			wp_die( $view->render( compact( 'status' ) ), $title, 400 );
			return;
		}

		$prompt_user = new Prompt_User( $user );

		$list = null;
		if ( isset( $args['l'] ) ) {
			$list = Prompt_Subscribing::make_subscribable_from_slug( sanitize_text_field( $args['l'] ) );
		}

		if ( ! $list ) {
			$prompt_user->delete_all_subscriptions();
			$status = sprintf(
				__( 'Got it. %s has been unsubscribed from all future mailings.', 'Postmatic' ),
				$user->user_email
			);
			wp_die( $view->render( compact( 'status' ) ), $title, 200 );
			return;
		}

		$list->unsubscribe( $prompt_user->id() );

		$status = sprintf(
			/* translators: %1$s is email, %2$s list URL, %3$s list label */
			__( 'Got it. %1$s has been unsubscribed from <a href="%2$s">%3$s</a>.', 'Postmatic' ),
			$list->subscription_url(),
			$list->subscription_object_label()
		);
		wp_die( $view->render( compact( 'status' ) ), $title, 200 );

	}

	/**
	 * A signer using the internal key.
	 *
	 * @since 2.1.0
	 * @return Prompt_Signer
	 */
	protected static function signer() {
		return new Prompt_Signer( new Prompt_Hasher( Prompt_Core::$options->get( 'internal_key' ) ) );
	}
}