<?php

/**
 * Handle WordPress events that could trigger mailings.
 */
class Prompt_Outbound_Handling {

	/**
	 * Any time a post is published schedule notifications.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 */
	public static function action_transition_post_status( $new_status, $old_status, $post ) {

		if ( ! Prompt_Core::$options->get( 'enable_post_delivery' ) ) {
			return;
		}

		if ( 'publish' == $old_status or 'publish' != $new_status ) {
			return;
		}

		// There is no way to suppress mailing when restoring a trashed post, so we always do it
		if ( 'trash' == $old_status ) {
			return;
		}

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING ) {
			return;
		}

		if ( self::ignore_published_post( $post->ID ) ) {
			return;
		}

		$prompt_post = new Prompt_Post( $post );

		if ( ! $prompt_post->unsent_recipient_ids() or Prompt_Admin_Delivery_Metabox::suppress_email( $post->ID ) ) {
			return;
		}

		Prompt_Post_Mailing::send_notifications( $post );
	}

	/**
	 * When a comment is published notify subscribers if needed.
	 *
	 * @param int $id
	 * @param object $comment
	 */
	public static function action_wp_insert_comment( $id, $comment ) {

		if ( ! Prompt_Core::$options->get( 'enable_comment_delivery' ) ) {
			return;
		}

		if ( $comment->comment_approved != '1'  or !empty( $comment->comment_type ) ) {
			return;
		}

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING )
			return;

		if ( ! apply_filters( 'prompt/comment_notifications/allow', true, $id ) ) {
			return;
		}

		Prompt_Comment_Mailing::send_notifications( $id );
	}

	/**
	 * When a comment is approved notify subscribers if needed.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $comment
	 */
	public static function action_transition_comment_status( $new_status, $old_status, $comment ) {

		if ( ! Prompt_Core::$options->get( 'enable_comment_delivery' ) ) {
			return;
		}

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING ) {
			return;
		}

		if ( 'approved' != $new_status or $old_status == $new_status or !empty( $comment->comment_type ) ) {
			return;
		}

		if ( ! apply_filters( 'prompt/comment_notifications/allow', true, $comment->comment_ID ) ) {
			return;
		}

		Prompt_Comment_Mailing::send_notifications( $comment );
	}

	/**
	 * Override native comment notifications.
	 *
	 * @since 1.4.4
	 *
	 * @link https://developer.wordpress.org/reference/hooks/comment_notification_recipients/
	 *
	 * @param array $addresses
	 * @return array Empty array to short circuit native notifications.
	 */
	public static function filter_comment_notification_recipients( $addresses ) {

		if ( Prompt_Core::$options->get( 'auto_subscribe_authors' ) ) {
			// Posmatic will send its own notifications on the transition_comment_status hook
			return array();
		}

		return $addresses;
	}

	/**
	 * Whether to ignore a published post.
	 *
	 * Currently only ignores Polylang translations.
	 *
	 * @param $post_id
	 * @return bool
	 */
	protected static function ignore_published_post( $post_id ) {

		if ( self::is_wpml_translation( $post_id ) ) {
			return true;
		}

		if ( ! function_exists( 'pll_default_language' ) )
			return false;

		$default_slug = pll_default_language( 'slug' );

		$post_slug = pll_get_post_language( $post_id, 'slug' );

		return ( $default_slug !== $post_slug );
	}

	/**
	 * Whether a post is a WPML post in a language other than the default.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id
	 * @return bool
	 */
	protected static function is_wpml_translation( $post_id ) {

		$wpml_language_code = apply_filters( 'wpml_default_language', null );

		if ( !$wpml_language_code ) {
			return false;
		}

		$wpml_language_code = isset( $wpml_language_code['language_code'] ) ? $wpml_language_code['language_code'] : $wpml_language_code;

		$post_language = apply_filters( 'wpml_post_language_details', null, $post_id );

		if ( !$post_language or empty( $post_language['language_code'] ) ) {
			return false;
		}

		return ( $wpml_language_code != $post_language['language_code'] );
	}

}
