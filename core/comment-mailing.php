<?php

class Prompt_Comment_Mailing {

	/**
	 * Send notifications appropriate for a newly published comment.
	 *
	 * Top level comments go to all post subscribers, replies optionally to the replyee.
	 *
	 * @param object|int $comment_id_or_object
	 * @param string $chunk Optional identifier for this chunk (to avoid cron collisions)
	 * @param int $retry_wait_seconds Minimum time to wait if a retry is necessary, or null to disable retry
	 */
	public static function send_notifications(
		$comment_id_or_object,
		$chunk = '',
		$retry_wait_seconds = null
	) {

		$comment = get_comment( $comment_id_or_object );

		self::handle_new_subscriber( $comment );

		$batch = new Prompt_Comment_Email_Batch( $comment );

		Prompt_Factory::make_mailer( $batch, null, $chunk )->set_retry_wait_seconds( $retry_wait_seconds )->send();
	}

	/**
	 * Send a comment a notification when their comment is rejected.
	 *
	 * This could be due to a deleted post, change in post status, or comments being closed.
	 *
	 * @param $user_id
	 * @param $post_id
	 */
	public static function send_rejected_notification( $user_id, $post_id ) {

		$comment_author = get_userdata( $user_id );
		$post = get_post( $post_id );
		$post_title = $post ? $post->post_title : __( 'a deleted post', 'Postmatic' );

		$template_data = compact( 'comment_author', 'post', 'post_title' );
		/**
		 * Filter comment rejected email template data.
		 *
		 * @param array $template_data {
		 *      @type WP_User $comment_author
		 *      @type WP_Post $post
		 *      @type string $post_title Post title or placeholder if post no longer exists
		 * }
		 */
		$template_data = apply_filters( 'prompt/comment_rejected_email/template_data', $template_data );

		$subject = sprintf( __( 'Unable to publish your reply to "%s"', 'Postmatic' ), $post_title );
		$template = new Prompt_Template( 'comment-rejected-email.php' );

		$batch = Prompt_Email_Batch::make_for_single_recipient( array(
			'to_address' => $comment_author->user_email,
			'subject' => $subject,
			'html_content' => $template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		) );

		/**
		 * Filter comment rejected email.
		 *
		 * @param Prompt_Email_Batch $batch
		 * @param array $template_data see prompt/comment_reject_email/template_data
		 */
		$batch = apply_filters( 'prompt/comment_rejected_batch', $batch, $template_data );

		Prompt_Factory::make_mailer( $batch )->send();
	}

	/**
	 * Handle the situation when a moderated comment subscribe request has not yet been fulfilled.
	 * @param $comment
	 */
	protected static function handle_new_subscriber( $comment ) {

		if ( ! Prompt_Comment_Form_Handling::subscription_requested( $comment ) )
			return;

		Prompt_Comment_Form_Handling::subscribe_commenter( $comment );
	}

}