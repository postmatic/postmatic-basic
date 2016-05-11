<?php

class Prompt_Post_Mailing {

	/**
	 * Send email notifications for a post.
	 *
	 * @param WP_Post|int $post
	 * @param string $chunk Optional identifier for this chunk (to avoid cron collisions)
	 * @param int $retry_wait_seconds Minimum time to wait if a retry is necessary, null for default
	 */
	public static function send_notifications( $post, $chunk = '', $retry_wait_seconds = null ) {

		$batch = new Prompt_Post_Email_Batch( new Prompt_Post_Rendering_Context( $post ) );

		Prompt_Factory::make_mailer( $batch, null, $chunk )->set_retry_wait_seconds( $retry_wait_seconds )->send();
	}

}