<?php

class Prompt_User_Handling {

	/**
	 * When a user is deleted, remove their subscriptions.
	 *
	 * Hooks deleted_user.
	 * @param $id
	 */
	public static function delete_subscriptions( $id ) {
		$prompt_user = new Prompt_User( $id );
		$prompt_user->delete_all_subscriptions();
	}

	/**
	 * When a profile is displayed, include Prompt user options.
	 *
	 * Hooks edit_user_profile, show_user_profile
	 * @param $user
	 */
	public static function render_profile_options( $user ) {
		$prompt_user = new Prompt_User( $user );
		echo $prompt_user->profile_options();
	}

	/**
	 * When a profile is saved, also save changes to our options.
	 *
	 * Hooks edit_user_profile_update, personal_options_update
	 * @param $user_id
	 */
	public static function update_profile_options( $user_id ) {
		$prompt_user = new Prompt_User( $user_id );
		$prompt_user->update_profile_options( $_POST );
	}

	/**
	 * Create a new subscriber user for an email address.
	 *
	 * Sends a welcome email to the new user.
	 *
	 * @param string email
	 * @return int|WP_Error The user ID or an error object.
	 */
	public static function create_from_email( $email ) {

		$password = wp_generate_password();

		$suffix = '';
		$basename = substr( $email, 0, strpos( $email, '@' ) );
		do {
			$username = $basename . $suffix;
			$suffix += 1;
		} while ( username_exists( $username ) );

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) )
			return $user_id;

		if ( Prompt_Core::$options->get( 'send_login_info' ) ) {
			$template = new Prompt_Template( 'new-user-email.php' );
			Prompt_User_Mailing::send_new_user_notification( $user_id, $password, $template );
		}

		return $user_id;
	}

	/**
	 * Get the logged in OR current commenter user.
	 *
	 * @return WP_User Null if not found
	 */
	public static function current_user() {
		$user = wp_get_current_user();

		if ( $user->exists() )
			return $user;

		$commenter = wp_get_current_commenter();

		if ( empty( $commenter['comment_author_email'] ) )
			return null;

		$user = get_user_by( 'email', $commenter['comment_author_email'] );

		if ( !$user )
			return null;

		return $user;
	}

	/**
	 * Prevent subscribers who were not sent credentials from resetting their password.
	 *
	 * Hooks allow_password_reset
	 *
	 * @since 1.3.2
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public static function filter_allow_password_reset( $allow, $user_id ) {

		$prompt_user = new Prompt_User( $user_id );

		if ( ! $prompt_user->get_wp_user()->has_cap( 'subscriber' ) )
			return $allow;

		if ( ! $prompt_user->get_subscriber_origin() )
			return $allow;

		if ( Prompt_Core::$options->get( 'send_login_info' ) )
			return $allow;

		return false;
	}
}