<?php

/**
 * Comment command for a new post
 *
 * Just like comment commands except for unsubscribe replies,
 * which unsubscribe from the post author or site rather than post comments.
 */
class Prompt_New_Post_Comment_Command extends Prompt_Comment_Command {

	/**
	 * Unsubscribe from the post author or site.
	 * @param boolean $notify
	 * @return array
	 */
	protected function unsubscribe( $notify = true ) {

		$prompt_post = new Prompt_Post( $this->post_id );
		$prompt_author = new Prompt_User( $prompt_post->get_wp_post()->post_author );

		if ( $prompt_author->is_subscribed( $this->user_id ) ) {
			$this->author_unsubscribe( $prompt_author, $notify );
			return;
		}

		// The user was not subscribed to the post author, so unsubscribe them from the site.

		$prompt_site = new Prompt_Site();

		$prompt_site->unsubscribe( $this->user_id );

		if ( $notify ) {
			Prompt_Subscription_Mailing::send_unsubscription_notification( $this->user_id, $prompt_site );
		}
	}

	/**
	 * Unsubscribe the user from an author.
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_User $author
	 * @param bool|true $notify
	 */
	protected function author_unsubscribe( Prompt_User $author, $notify = true ) {

		$author->unsubscribe( $this->user_id );

		if ( $notify ) {
			Prompt_Subscription_Mailing::send_unsubscription_notification( $this->user_id, $author );
		}
	}

}