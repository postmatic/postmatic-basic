<?php

/**
 * Handle GET requests for Postmatic routes, e.g. unsubscribes.
 *
 * @since 2.1.0
 */
class Prompt_Routing {

	/** @var string  */
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
		$args = array( self::$route_query_var => 'unsubscribe', 'u' => $user_id );

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

		$view = new Prompt_Template( 'unsubscribe-view.php' );
		$context = array( 'is_valid' => true, 'user' => null, 'list' => null );
		$title = __( 'Unsubscribe', 'Postmatic' );

		$user = isset( $args['u'] ) ? get_user_by( 'id', intval( $args['u'] ) ) : null;

		if ( ! self::signer()->is_valid( $args ) or ! $user ) {
			$context['is_valid'] = false;
			wp_die( $view->render( $context ), $title, 400 );
			return; // in case there's a die handler that doesn't die
		}

		$prompt_user = new Prompt_User( $user );
		$context['user'] = $prompt_user;

		$list = null;
		if ( isset( $args['l'] ) ) {
			$list = Prompt_Subscribing::make_subscribable_from_slug( sanitize_text_field( $args['l'] ) );
		}

		if ( ! $list ) {
			$prompt_user->delete_all_subscriptions();
			wp_die( $view->render( $context ), $title, 200 );
			return; // in case there's a die handler that doesn't die
		}

		$context['list'] = $list;
		$list->unsubscribe( $prompt_user->id() );

		wp_die( $view->render( $context ), $title, 200 );

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