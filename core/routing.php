<?php

/**
 * Handles GET requests for Postmatic routes, e.g. unsubscribes.
 *
 * @since 2.1.0
 */
class Prompt_Routing {

	/** @var string  */
	protected static $route_query_var = 'postmatic_route';

	/**
	 * Adds the Postmatic route query variable.
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
	 * Routes requests including the routing query variable.
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
	 * Gets an unsubscribe url for one or all lists.
	 *
	 * @since 2.1.0
	 * @param int $user_id The ID of the user to unsubscribe.
	 * @param string $list_slug Optional. The slug of a list to unsubscribe from. Default unsubscribes from all lists.
	 * @return string The unsubscribe URL.
	 */
	public static function unsubscribe_url( $user_id, $list_slug = '' ) {
		$args = array( self::$route_query_var => 'unsubscribe', 'u' => $user_id );

		if ( $list_slug ) {
			$args['l'] = $list_slug;
		}

		return self::signer()->sign_url( home_url(), $args );
	}

	/**
	 * Gets an opt-in url for a list.
	 *
	 * @since 2.1.0
	 * @param Prompt_Register_Subscribe_Command $command The registration command for the potential new subscriber.
	 * @return string The opt-in URL.
	 */
	public static function opt_in_url( Prompt_Register_Subscribe_Command $command) {
		$args = array( self::$route_query_var => 'opt_in' );

		$command_keys = $command->get_keys();
		$args['c'] = $command_keys[0];

		return self::signer()->sign_url( home_url(), $args );
	}

	/**
	 * Handles an opt-in request and exit.
	 *
	 * @since 2.1.0
	 * @param array $args {
	 *      Query string arguments for the opt-in request.
	 *
	 *      @type string $c The ID of the comment where the subscriber data was stashed.
	 *      @type string $t The request token.
	 *      @type string $s The request signature.
	 * }
	 */
	protected static function opt_in( $args ) {

		$view = new Prompt_Template( 'opt-in-view.php' );
		$context = array( 'list' => null );
		$title = __( 'Opt In', 'Postmatic' );

		$stash_id = isset( $args['c'] ) ? intval( $args['c'] ) : null;

		if ( ! self::signer()->is_valid( $args ) or ! $stash_id ) {
			wp_die( $view->render( $context ), $title, 400 );
			return; // in case there's a die handler that doesn't die
		}

		$message = new stdClass();
		$message->message = Prompt_Agree_Matcher::target();

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $stash_id ) );
		$command->set_message( $message );

		$context['list'] = $command->execute( false );

		wp_die( $view->render( $context ), $title, 200 );
	}

	/**
	 * Handle an unsubscribe request and exit.
	 *
	 * @since 2.1.0
	 * @param array $args {
	 *      Query string arguments for the opt-in request.
	 *
	 *      @type string $u The ID of the user to unsubscribe
	 *      @type string $l The slug of a list to unsubscribe from, or all lists if empty
	 *      @type string $t The request token
	 *      @type string $s The request signature
	 * }
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