<?php

/**
 * Allow users of a comment-flooded post to rejoin the discussion.
 * @since 1.0.0
 */
class Prompt_Comment_Flood_Command extends Prompt_Comment_Command {

	protected static $rejoin_method = 'rejoin';
	protected static $ignore_method = 'ignore';

	/**
	 * @since 1.0.0
	 * @return string
	 */
	function get_text_command() {

		$message_text = $this->get_message_text();

		if ( preg_match( '/^\s*$/', $message_text, $matches ) ) {
			return self::$rejoin_method;
		}

		$matcher = new Prompt_Rejoin_Matcher( $message_text );
		
		if ( $matcher->matches() ) {
			return self::$rejoin_method;
		}

		return self::$ignore_method;
	}

	/**
	 * @since 1.0.0
	 */
	protected function rejoin() {

		$prompt_post = new Prompt_Post( $this->post_id );

		if ( $prompt_post->is_subscribed( $this->user_id ) )
			return;

		$prompt_post->subscribe( $this->user_id );

		Prompt_Subscription_Mailing::send_rejoin_notification( $this->user_id, $prompt_post );

		return;
	}

	/**
	 * @since 1.0.0
	 */
	function ignore() {
		// We're ignoring any message content but the rejoin command
	}
}