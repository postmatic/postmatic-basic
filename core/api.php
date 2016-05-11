<?php

/**
 * A facade for documented integration points.
 * @since 1.0.0
 */
class Prompt_Api {
	const INVALID_EMAIL = 'invalid_email';
	const ALREADY_SUBSCRIBED = 'already_subscribed';
	const CONFIRMATION_SENT = 'confirmation_sent';
	const OPT_IN_SENT = 'opt_in_sent';
	const NEVER_SUBSCRIBED = 'never_subscribed';
	const ALREADY_UNSUBSCRIBED = 'already_unsubscribed';

	/**
	 * Subscribe an email address to a list.
	 *
	 * @since 2.0.0 lists parameter.
	 * @since 1.0.0
	 *
	 * @param array $subscriber_data {
	 *     User data fields, use any from @see wp_insert_user, most likely of interest:
	 *
	 * @type string $email_address Required.
	 * @type string $first_name
	 * @type string $last_name
	 * @type string $display_name
	 * }
	 * @param string|string[] $list_slugs Optional, default is the
	 *   currently enabled sign-up lists on the site. An array will result in an opt-in email that will allow
	 *   the subscriber to choose which one they want.
	 * @return string The resulting status, one of:
	 *   Prompt_Api::INVALID_EMAIL
	 *   Prompt_Api::ALREADY_SUBSCRIBED
	 *   Prompt_Api::CONFIRMATION_SENT      for existing user email addresses
	 *   Prompt_Api::OPT_IN_SENT            for unrecognized email addresses
	 */
	public static function subscribe( $subscriber_data, $list_slugs = null ) {

		if ( !is_array( $subscriber_data ) ) {
			$subscriber_data = array( 'user_email' => $subscriber_data );
		}

		// Translate the friendlier email_address to user_email
		if ( isset( $subscriber_data['email_address'] ) ) {
			$subscriber_data['user_email'] = $subscriber_data['email_address'];
			unset( $subscriber_data['email_address'] );
		}

		$subscriber_data['user_email'] = sanitize_email( $subscriber_data['user_email'] );

		$email_address = $subscriber_data['user_email'];

		if ( !is_email( $email_address ) ) {
			return self::INVALID_EMAIL;
		}

		$lists = self::resolve_lists( $list_slugs );

		$user = get_user_by( 'email', $email_address );

		if ( !$user or count( $lists ) > 1 ) {

			self::ensure_display_name( $subscriber_data );

			Prompt_Subscription_Mailing::send_agreement( $lists, $email_address, $subscriber_data );

			return self::OPT_IN_SENT;
		}
		
		$list = $lists[0];

		if ( $list->is_subscribed( $user->ID ) ) {
			return self::ALREADY_SUBSCRIBED;
		}

		$list->subscribe( $user->ID );

		Prompt_Subscription_Mailing::send_subscription_notification( $user->ID, $list );

		return self::CONFIRMATION_SENT;
	}

	/**
	 * Unsubscribe an email address from a list.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email_address The address to unsubscribe
	 * @param string|string[] $list_slugs Optional, default is all subscribed lists.
	 * @return string The resulting status, one of:
	 *   Prompt_Api::NEVER_SUBSCRIBED       we don't recognize the email address
	 *   Prompt_Api::ALREADY_UNSUBSCRIBED
	 *   Prompt_Api::CONFIRMATION_SENT
	 */
	public static function unsubscribe( $email_address, $list_slugs = null ) {

		$user = get_user_by( 'email', $email_address );

		if ( !$user ) {
			return self::NEVER_SUBSCRIBED;
		}

		if ( !$list_slugs ) {
			return self::unsubscribe_all( $user );
		}

		$lists = self::resolve_lists( $list_slugs );

		$result = self::ALREADY_UNSUBSCRIBED;

		foreach ( $lists as $list ) {
			if ( $list->is_subscribed( $user->ID ) ) {
				$list->unsubscribe( $user->ID );
				Prompt_Subscription_Mailing::send_unsubscription_notification( $user->ID, $list );
				$result = self::CONFIRMATION_SENT;
			}
		}

		return $result;
	}

	/**
	 * Make a display name from other data if missing.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data display_name is added when missing
	 * @return string
	 */
	protected static function ensure_display_name( &$data ) {

		$name = isset( $data['display_name'] ) ? $data['display_name'] : '';

		if ( $name )
			return $name;

		$names = array();

		if ( isset( $data['first_name'] ) )
			$names[] = $data['first_name'];

		if ( isset( $data['last_name'] ) )
			$names[] = $data['last_name'];

		$data['display_name'] = implode( ' ', $names );

		return $data['display_name'];
	}

	protected static function resolve_lists( $list_slugs ) {

		if ( !$list_slugs ) {
			return Prompt_Subscribing::get_signup_lists();
		}

		if ( is_object( $list_slugs ) ) {
			return array( $list_slugs );
		}

		if ( !is_array( $list_slugs ) ) {
			$list_slugs = array( $list_slugs );
		}

		return array_map( array( __CLASS__, 'resolve_list' ), $list_slugs );
	}

	protected static function resolve_list( $list_slug ) {

		if ( is_object( $list_slug ) ) {
			return $list_slug;
		}

		return Prompt_Subscribing::make_subscribable_from_slug( $list_slug );
	}

	protected static function unsubscribe_all( WP_User $user ) {

		$prompt_user = new Prompt_User( $user );

		$lists = $prompt_user->delete_all_subscriptions();

		if ( !$lists ) {
			return self::ALREADY_UNSUBSCRIBED;
		}

		Prompt_Subscription_Mailing::send_unsubscription_notification( $user->ID, $lists[0] );

		return self::CONFIRMATION_SENT;
	}
	
}
