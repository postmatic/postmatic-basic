<?php

/**
 * Handle requests for Postmatic views, e.g. unsubscribes.
 */
class Prompt_View_Handling {

	protected static $view_query_var = 'postmatic_view';

	public static function add_query_vars( $query_vars ) {
		$query_vars[] = self::$view_query_var;
		return $query_vars;
	}

	public static function template_redirect() {
		$view = get_query_var( self::$view_query_var );

		if ( ! $view ) {
			return;
		}

		$view_class = 'Prompt_' . ucfirst( $view ) . '_Template';

		if ( ! class_exists( $view_class ) ) {
			wp_redirect( home_url() );
			return;
		}

		$template = new $view_class();

		$template->render( $_GET, true );

		exit();
	}

	public static function view_url( $view ) {
		return add_query_arg( array( self::$view_query_var => $view ), home_url() );
	}
}