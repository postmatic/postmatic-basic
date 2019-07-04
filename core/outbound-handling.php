<?php

/**
 * Handle WordPress events that could trigger mailings.
 */
class Prompt_Outbound_Handling {

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

		$approved_types = array(
			'comment',
		);

		/**
		 * Filter: replyable/comment_email_batch/approved_types
		 *
		 * Allow extra comment types to have moderation emails sent.
		 *
		 * @since 2.2.5
		 *
		 * @param array  Approved Comment Types.
		 */
		$approved_types = apply_filters( 'replyable/comment_email_batch/approved_types', $approved_types ); //phpcs:ignore

		if ( $comment->comment_approved != '1' || ! in_array( $comment->comment_type, $approved_types, true ) ) {
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

		$approved_types = array(
			'comment',
		);

		/**
		 * Filter: replyable/comment_email_batch/approved_types
		 *
		 * Allow extra comment types to have moderation emails sent.
		 *
		 * @since 2.2.5
		 *
		 * @param array  Approved Comment Types.
		 */
		$approved_types = apply_filters( 'replyable/comment_email_batch/approved_types', $approved_types ); //phpcs:ignore

		if ( ! Prompt_Core::$options->get( 'enable_comment_delivery' ) ) {
			return;
		}

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING ) {
			return;
		}

		if ( 'approved' != $new_status or $old_status == $new_status or ! in_array( get_comment_type( $comment ), $approved_types, true ) ) {
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


}
