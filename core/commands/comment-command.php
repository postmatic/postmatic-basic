<?php

/**
 * Command for replies that can be turned into a post comment.
 *
 * @since 1.0.0
 */
class Prompt_Comment_Command implements Prompt_Interface_Command {

	protected static $subscribe_method = 'subscribe';
	protected static $unsubscribe_method = 'unsubscribe';

	/** @var array */
	protected $keys = array( 0, 0 );
	/** @var  int */
	protected $post_id;
	/** @var  int */
	protected $user_id;
	/** @var  object */
	protected $message;
	/** @var  int */
	protected $parent_comment_id;
	/** @var  string */
	protected $message_text;

	/**
	 * Set the array of key metadata
	 * @since 1.0.0
	 * @param array $keys [ $post_id, $user_id, $parent_comment_id ]
	 */
	public function set_keys( $keys ) {
		$this->keys = $keys;
	}

	/**
	 * Get key metadata
	 * @since 1.0.0
	 * @return array [ $post_id, $user_id, $parent_comment_id ]
	 */
	public function get_keys() {
		return $this->keys;
	}

	/**
	 * Set the command message data
	 * @since 1.0.0
	 * @param object $message {
	 * @type string $message
	 * }
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Get the command message data
	 * @since 1.0.0
	 * @return object {
	 * @type string $message
	 * }
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Execute the operation specified by the command data
	 * @since 1.0.0
	 */
	public function execute() {

		if ( !$this->validate() )
			return;

		$text_command = $this->get_text_command();
		if ( $text_command ) {
			$this->$text_command( $notify = true );
			return;
		}

		$this->add_comment();
	}

	/**
	 * Set the post ID key
	 * @since 1.0.0
	 * @param int $id
	 */
	public function set_post_id( $id ) {
		$this->post_id = intval( $id );
		$this->keys[0] = $this->post_id;
	}

	/**
	 * Set user ID key
	 * @since 1.0.0
	 * @param int $id
	 */
	public function set_user_id( $id ) {
		$this->user_id = intval( $id );
		$this->keys[1] = $this->user_id;
	}

	/**
	 * Set the parent comment ID key
	 * @since 1.0.0
	 * @param int $id
	 */
	public function set_parent_comment_id( $id ) {
		$this->parent_comment_id = intval( $id );
		$this->keys[2] = $this->parent_comment_id;
	}

	/**
	 * Parse and validate the array of keys
	 * @since 1.0.0
	 * @return bool
	 */
	protected function validate() {

		if ( !is_array( $this->keys ) or count( $this->keys ) < 2 ) {
			trigger_error( __( 'Invalid comment keys', 'Postmatic' ), E_USER_WARNING );
			return false;
		}

		// Ensure back compatibility with beta versions that did not include parent comment ID
		if ( count( $this->keys ) == 2 )
			$this->keys[2] = 0;

		if ( empty( $this->message ) ) {
			trigger_error( __( 'Invalid message', 'Postmatic' ), E_USER_WARNING );
			return false;
		}

		$this->post_id = $this->keys[0];
		$this->user_id = $this->keys[1];
		$this->parent_comment_id = $this->keys[2];

		return true;
	}

	/**
	 * Get the message text
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_message_text() {
		if ( !$this->message_text ) {
			$this->message_text = $this->message->message;
		}

		return $this->message_text;
	}

	/**
	 * Get text command from the message, if any.
	 *
	 * A blank message is treated as a subscribe command.
	 *
	 * @since 1.0.0
	 * @return string Text command if found, otherwise empty.
	 */
	protected function get_text_command() {

		$stripped_text = $this->get_message_text();

		if ( preg_match( '/^\s*$/', $stripped_text, $matches ) ) {
			return self::$subscribe_method;
		}

		$subscribe_matcher = new Prompt_Subscribe_Matcher( $stripped_text );
		if ( $subscribe_matcher->matches() ) {
			return self::$subscribe_method;
		}

		$unsubscribe_matcher = new Prompt_Unsubscribe_Matcher( $stripped_text );
		if ( $unsubscribe_matcher->matches() ) {
			return self::$unsubscribe_method;
		}

		return '';
	}

	/**
	 * Subscribe the user to comments on the post.
	 * @since 1.0.0
	 * @param bool $notify Whether to send a subscription notification to the user
	 */
	protected function subscribe( $notify = false ) {

		$prompt_post = new Prompt_Post( $this->post_id );

		if ( $prompt_post->is_subscribed( $this->user_id ) ) {
			return;
		}

		if (
			Prompt_Core::$options->get( 'auto_subscribe_authors' ) and
			$this->user_id == $prompt_post->get_wp_post()->post_author
		) {
			return;
		}

		$prompt_post->subscribe( $this->user_id );

		if ( $notify ) {
			Prompt_Subscription_Mailing::send_subscription_notification( $this->user_id, $prompt_post );
		}
	}

	/**
	 * Unsubscribe the user from new post comments.
	 * @since 1.0.0
	 */
	protected function unsubscribe() {

		$prompt_post = new Prompt_Post( $this->post_id );

		if ( !$prompt_post->is_subscribed( $this->user_id ) ) {
			return;
		}

		$prompt_post->unsubscribe( $this->user_id );

		Prompt_Subscription_Mailing::send_unsubscription_notification( $this->user_id, $prompt_post );

		return;
	}

	/**
	 * Add a comment based on the message text.
	 * @since 1.0.0
	 */
	protected function add_comment() {

		$text = $this->get_message_text();

		$post = get_post( $this->post_id );

		if ( !$post or 'publish' != $post->post_status or !comments_open( $this->post_id ) ) {
			trigger_error(
				sprintf( __( 'rejected comment on unqualified post %s', 'Postmatic' ), $this->post_id ),
				E_USER_NOTICE
			);
			Prompt_Comment_Mailing::send_rejected_notification( $this->user_id, $this->post_id );
			return;
		}

		if ( $this->comment_exists( $text ) ) {
			trigger_error(
				sprintf( __( 'rejected duplicate comment on %s', 'Postmatic' ), $this->post_id ),
				E_USER_NOTICE
			);
			return;
		}

		$this->subscribe( $notify = false );

		$user = get_userdata( $this->user_id );
		$comment_data = array(
			'user_id' => $this->user_id,
			'comment_post_ID' => $this->post_id,
			'comment_content' => $text,
			'comment_agent' => __CLASS__,
			'comment_author' => $user->display_name,
			'comment_author_IP' => '',
			'comment_author_url' => $user->user_url,
			'comment_author_email' => $user->user_email,
			'comment_parent' => $this->parent_comment_id,
			'comment_type' => '',
			'comment_date_gmt' => current_time( 'mysql', 1 ),
		);

		remove_all_actions( 'check_comment_flood' );

		$comment_data = wp_filter_comment( $comment_data );

		$comment_data['comment_approved'] = $this->approve_comment( $comment_data );

		$comment_id = wp_insert_comment( $comment_data );

		if ( 0 == $comment_data['comment_approved'] )
			wp_notify_moderator( $comment_id );

	}

	/**
	 * Our own duplicate check that does not die on failure.
	 *
	 * @param $text
	 * @return bool
	 */
	protected function comment_exists( $text ) {
		global $wpdb;

		// Simple duplicate check
		$dupe = $wpdb->prepare(
			"SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND user_id = %s AND comment_approved != 'trash' AND comment_content = %s LIMIT 1",
			$this->post_id,
			$this->user_id,
			$text
		);

		return $wpdb->get_var( $dupe );
	}

	/**
	 * Similar to wp_approve_comment(), but does not check for duplicates or die on failure.
	 *
	 * @since 1.4.7
	 *
	 * @param $commentdata
	 * @return int 1 for approved, 0 for not approved, 'spam' for spam
	 */
	protected function approve_comment( $commentdata ) {

		$user = get_user_by( 'id', $this->user_id );
		$post = get_post( $this->post_id );

		if ( isset( $user ) && ( $commentdata['user_id'] == $post->post_author || $user->has_cap( 'moderate_comments' ) ) ) {
			// The author and the admins get respect.
			$approved = 1;
		} else {
			// Everyone else's comments will be checked.
			if ( check_comment(
				$commentdata['comment_author'],
				$commentdata['comment_author_email'],
				$commentdata['comment_author_url'],
				$commentdata['comment_content'],
				$commentdata['comment_author_IP'],
				$commentdata['comment_agent'],
				$commentdata['comment_type']
			) ) {
				$approved = 1;
			} else {
				$approved = 0;
			}

			if ( wp_blacklist_check(
				$commentdata['comment_author'],
				$commentdata['comment_author_email'],
				$commentdata['comment_author_url'],
				$commentdata['comment_content'],
				$commentdata['comment_author_IP'],
				$commentdata['comment_agent']
			) ) {
				$approved = 'spam';
			}
		}

		/**
		 * Filter a comment's approval status before it is set.
		 *
		 * @since 2.1.0
		 *
		 * @param bool|string $approved The approval status. Accepts 1, 0, or 'spam'.
		 * @param array $commentdata Comment data.
		 */
		$approved = apply_filters( 'pre_comment_approved', $approved, $commentdata );
		return $approved;
	}

}