<?php

/**
 * An email batch that knows how to render a comment for emails.
 *
 * @since 2.0.0
 *
 */
class Prompt_Comment_Email_Batch extends Prompt_Email_Batch {


	/** @var  Prompt_Comment */
	protected $prompt_comment;
	/** @var  Prompt_Post */
	protected $prompt_post;
	/** @var  string */
	protected $subscribed_post_title_link;
	/** @var  WP_User */
	protected $parent_author;
	/** @var  string */
	protected $parent_author_name;
	/** @var  Prompt_Comment */
	protected $prompt_parent_comment;
	/** @var  Prompt_User */
	protected $recipient;
	/** @var bool */
	protected $replyable;
	/** @var  Prompt_Comment_Flood_Controller */
	protected $flood_controller;
	/** @var  array */
	protected $previous_comments;

	/**
	 * Builds an email batch with content and recipients based on a comment.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Comment $comment Target comment
	 * @param Prompt_Comment_Flood_Controller
	 */
	public function __construct( $comment, Prompt_Comment_Flood_Controller $flood_controller = null ) {

		$this->prompt_comment = new Prompt_Comment( $comment );

		$this->prompt_post = $prompt_post = new Prompt_Post( $comment->comment_post_ID );

		$this->flood_controller = $flood_controller;
		if ( ! $this->flood_controller ) {
			$this->flood_controller = Prompt_Factory::make_comment_flood_controller( $comment );
		}

		$this->subscribed_post_title_link = html( 'a',
			array( 'href' => get_permalink( $this->prompt_post->id() ) ),
			Prompt_Formatting::escape_handlebars_expressions( get_the_title( $this->prompt_post->id() ) )
		);

		$is_api_delivery = ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) );

		$parent_comment = $parent_author = null;
		$parent_author_name = '';
		$template_file = 'new-comment-email.php';

		if ( $comment->comment_parent ) {
			$this->prompt_parent_comment = new Prompt_Comment( $comment->comment_parent );
			$parent_comment = $this->prompt_parent_comment->get_wp_comment();
			$parent_author = $this->prompt_parent_comment->get_author_user();
			$parent_author_name = Prompt_Formatting::escape_handlebars_expressions(
				$this->prompt_parent_comment->get_author_name()
			);

			$template_file = 'comment-reply-email.php';
		}

		$this->parent_author = $parent_author;
		$this->parent_author_name = $parent_author_name;

		$post_author = get_userdata( $prompt_post->get_wp_post()->post_author );
		$post_author_name = $post_author ? $post_author->display_name : __( 'Anonymous', 'Postmatic' );
		$post_author_name = Prompt_Formatting::escape_handlebars_expressions( $post_author_name );

		$this->set_previous_comments();

		$template_data = array(
			'comment_ID' => $comment->comment_ID,
			'comment_author' => $this->prompt_comment->get_author_user(),
			'commenter_name' => $this->prompt_comment->get_author_name(),
			'comment_post_ID' => $comment->comment_post_ID,
			'comment_author_url' => $comment->comment_author_url,
			'comment_text' => Prompt_Formatting::escape_handlebars_expressions( wpautop( $comment->comment_content ) ),
			'avatar' => get_avatar( $comment ),
			'subscribed_post' => $prompt_post,
			'subscribed_post_author_name' => $post_author_name,
			'subscribed_post_title_link' => $this->subscribed_post_title_link,
			'previous_comments' => $this->previous_comments,
			'parent_author' => $parent_author,
			'parent_author_name' => $parent_author_name,
			'parent_comment' => $parent_comment,
			'comment_header' => true,
			'is_api_delivery' => $is_api_delivery,
		);

		/**
		 * Filter comment email template data.
		 *
		 * @param array $template_data {
		 * @type WP_User $comment_author
		 * @type string $commenter_name
		 * @type int $comment_post_ID
		 * @type string $commenter_author_url
		 * @type string $commenter_text
		 * @type string $avatar
		 * @type Prompt_post $subscribed_post
		 * @type string $subscribed_post_author_name
		 * @type string $subscribed_post_title_link
		 * @type array $previous_comments
		 * @type WP_User $parent_author
		 * @type string $parent_author_name
		 * @type object $parent_comment
		 * @type bool $comment_header
		 * @type bool $is_api_delivery
		 * }
		 */
		$template_data = apply_filters( 'prompt/comment_email_batch/template_data', $template_data );

		$html_template = new Prompt_Template( $template_file );
		$text_template = new Prompt_Text_Template( str_replace( '.php', '-text.php', $template_file ) );

		/* translators: %s is a subscription list title */
		$footnote_format = __(
			'You received this email because you\'re subscribed to %s.',
			'Postmatic'
		);
		$unsubscribe_href = $is_api_delivery ? $this->unsubscribe_mailto() : '{{{unsubscribe_url}}}';

		$message_template = array(
			'from_name' => $this->prompt_comment->get_author_name(),
			'text_content' => $text_template->render( $template_data ),
			'html_content' => $html_template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::COMMENT,
			'subject' => '{{{subject}}}',
			'reply_to' => Prompt_Core::$options->is_api_transport() ? '{{{reply_to}}}' : 'donotreply@gopostmatic.com',
			'footnote_html' => sprintf(
				$footnote_format . ' %s',
				$this->prompt_post->subscription_object_label(),
				"<a href=\"{$unsubscribe_href}\">" . Prompt_Unsubscribe_Matcher::target() . "</a>"
			),
			/* translators: %1$s is a subscription list title, %2$s is the unsubscribe command word */
			'footnote_text' => sprintf(
				$footnote_format . ' %s',
				$this->prompt_post->subscription_object_label( Prompt_Enum_Content_Types::TEXT ),
				Prompt_Unsubscribe_Matcher::target() . ": $unsubscribe_href"
			),
		);

		parent::__construct( $message_template );

		$recipient_ids = array_diff( $this->flood_controlled_recipient_ids(), $this->prompt_comment->get_sent_subscriber_ids() );

		/**
		 * Filter whether to send new comment notifications.
		 *
		 * @param boolean $send Default true.
		 * @param object $comment
		 * @param array $recipient_ids
		 */
		if ( ! apply_filters( 'prompt/send_comment_notifications', true, $this->prompt_comment, $recipient_ids ) )
			return null;

		$this->add_recipients( $recipient_ids );

	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return Prompt_Comment
	 */
	public function get_comment() {
		return $this->prompt_comment;
	}

	/**
	 * Add the IDs of users who have been sent an email notification for this comment.
	 *
	 * @since 2.0.0
	 */
	public function lock_for_sending() {

		$recipient_ids = wp_list_pluck( $this->individual_message_values, 'id' );

		$sent_ids = array_unique( array_merge( $this->prompt_comment->get_sent_subscriber_ids(), $recipient_ids ) );

		$this->prompt_comment->set_sent_subscriber_ids( $sent_ids );
	}

	/**
	 * Remove recorded sent IDs for a list of addresses.
	 *
	 * @since 2.0.11
	 *
	 * @param array $addresses
	 * @return $this;
	 */
	public function clear_failures( $addresses ) {

		$ids = array_map( array( $this, 'to_address_to_id' ), $addresses );

		$sent_ids = array_diff( $this->prompt_comment->get_sent_subscriber_ids(), $ids );

		$this->prompt_comment->set_sent_subscriber_ids( $sent_ids );

		return $this;
	}

	/**
	 * Record user IDs for a list of addresses that failed to send.
	 *
	 * @since 2.0.11
	 *
	 * @param array $addresses
	 * @return $this;
	 */
	public function record_failures( $addresses ) {

		$ids = array_map( array( $this, 'to_address_to_id' ), $addresses );

		$this->prompt_comment->add_failed_subscriber_ids( $ids );

		return $this;
	}


	/**
	 * Remove the IDs of users from the sent list so delivery can be retried.
	 *
	 * @since 2.0.0
	 */
	public function clear_for_retry() {

		$recipient_ids = wp_list_pluck( $this->individual_message_values, 'id' );

		$sent_ids = array_diff( $this->prompt_comment->get_sent_subscriber_ids(), $recipient_ids );

		$this->prompt_comment->set_sent_subscriber_ids( $sent_ids );
	}

	/**
	 * Add recipient-specific values for an email.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $recipient
	 * @return $this
	 */
	protected function add_recipient( WP_User $recipient ) {

		$list_slug = Prompt_Subscribing::get_subscribable_slug( $this->prompt_post );

		$values = array(
			'id' => $recipient->ID,
			'to_name' => $recipient->display_name,
			'to_address' => $recipient->user_email,
			'subject' => $this->subscriber_subject( $recipient ),
			'unsubscribe_url' => Prompt_Routing::unsubscribe_url( $recipient->ID, $list_slug ),
			'subscriber_comment_intro_html' => $this->subscriber_comment_intro_html( $recipient ),
			'subscriber_comment_intro_text' => $this->subscriber_comment_intro_text( $recipient ),
		);

		if ( ! Prompt_Core::is_api_transport() and $recipient->ID == $this->prompt_post->get_wp_post()->post_author ) {
			$values['post_author_message'] = html(
				'div class="author_message" style="padding: 10px; background: #FFFBCC; font-weight: bold; margin: 15px 0; border-top: 1px dashed #ddd; border-bottom: 1px dashed #ddd; font-size: 11px;"',
				html(
					'strong',
					sprintf(
						__(
							'Hey %s - Authors who engage their readers through commenting are more likely to have better search engine placement, more traffic, and healthier blogs.',
							'Postmatic'
						),
						$recipient->display_name
					)
				),
				'<br /><br />',
				html(
					'a',
					array( 'href' => 'http://replyable.com/upgrade' ),
					__(
						'Upgrade Replyable to gain access to invaluable author tools, two-way email commenting (you could reply to this email to leave a followup comment!), and features for more engagement, more comments, and a happier community.',
						'Postmatic'
					)
				)
			);
		}

		if ( Prompt_Core::is_api_transport() ) {

			$command = new Prompt_Comment_Command();
			$command->set_post_id( $this->prompt_post->id() );
			$command->set_user_id( $recipient->ID );
			$command->set_parent_comment_id( $this->prompt_comment->id() );

			$values['reply_to'] = $this->trackable_address( Prompt_Command_Handling::get_command_metadata( $command ) );

			$values = array_merge(
				$values,
				Prompt_Command_Handling::get_comment_reply_macros( $this->previous_comments, $recipient->ID )
			);
		}

		return $this->add_individual_message_values( $values );
	}

	/**
	 * Set the previous approved comments array.
	 *
	 * Always includes the comment being mailed.
	 *
	 * If the comment is a reply, gets ancestor comments.
	 *
	 * If the comment is top level, gets previous top level comments.
	 *
	 * Adds an 'excerpt' property with a 100 word text excerpt.
	 *
	 * @since 2.0.0
	 *
	 * @param int $number
	 * @return $this
	 */
	protected function set_previous_comments( $number = 3 ) {

		if ( $this->prompt_parent_comment ) {
			$this->previous_comments = $this->comment_thread();
			return $this;
		}

		$comments = $this->previous_top_level_comments( $number );

		foreach ( $comments as $comment ) {
			$comment->excerpt = $this->excerpt( $comment );
		}

		$this->previous_comments = array_reverse( $comments );

		return $this;
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function comment_thread() {

		$comment = $this->prompt_comment->get_wp_comment();
		$comments = array( $comment );

		while ( $comment->comment_parent ) {
			$comment = get_comment( $comment->comment_parent );
			$comments[] = $comment;
		}

		return $comments;
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param int $number
	 * @return array
	 */
	protected function previous_top_level_comments( $number = 3 ) {
		$query = array(
			'post_id' => $this->prompt_post->id(),
			'parent' => 0,
			'status' => 'approve',
			'number' => $number,
			'date_query' => array(
				array(
					'before' => $this->prompt_comment->get_wp_comment()->comment_date,
					'inclusive' => true,
				)
			)
		);
		return get_comments( $query );
	}

	/**
	 * Make a 100 word excerpt of a comment.
	 *
	 * @since 2.0.0
	 *
	 * @param object $comment
	 * @param int $word_count
	 * @return string
	 */
	protected function excerpt( $comment, $word_count = 100 ) {

		$comment_text = strip_tags( $comment->comment_content );

		$words = explode( ' ', $comment_text );

		$elipsis = count( $words ) > $word_count ? ' &hellip;' : '';

		return implode( ' ', array_slice( $words, 0, $word_count ) ) . $elipsis;
	}


	/**
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $subscriber
	 * @return string
	 */
	protected function subscriber_subject( WP_User $subscriber ) {
		if ( $this->parent_author and $this->parent_author->ID == $subscriber->ID ) {
			return sprintf(
				__( '%s replied to your comment on %s.', 'Postmatic' ),
				$this->prompt_comment->get_author_name(),
				$this->prompt_post->get_wp_post()->post_title
			);
		}

		if ( $this->prompt_parent_comment ) {
			return sprintf(
				__( '%s replied to %s on %s', 'Postmatic' ),
				$this->prompt_comment->get_author_name(),
				$this->prompt_parent_comment->get_author_name(),
				$this->prompt_post->get_wp_post()->post_title
			);
		}

		return sprintf(
			__( '%s commented on %s', 'Postmatic' ),
			$this->prompt_comment->get_author_name(),
			$this->prompt_post->get_wp_post()->post_title
		);
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $subscriber
	 * @return string
	 */
	protected function subscriber_comment_intro_html( WP_User $subscriber ) {
		return $this->subscriber_comment_intro( $subscriber, Prompt_Enum_Content_Types::HTML );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $subscriber
	 * @return string
	 */
	protected function subscriber_comment_intro_text( WP_User $subscriber ) {
		return $this->subscriber_comment_intro( $subscriber, Prompt_Enum_Content_Types::TEXT );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $subscriber
	 * @param string $type Default HTML, alternately Prompt_Enum_Content_Types::TEXT.
	 * @return string
	 */
	protected function subscriber_comment_intro( WP_User $subscriber, $type = Prompt_Enum_Content_Types::HTML ) {

		$name = $this->prompt_comment->get_author_name();
		$parent_author_name = $this->parent_author_name;
		$title = $this->prompt_post->get_wp_post()->post_title;

		if ( Prompt_Enum_Content_Types::HTML === $type ) {
			$name = html( 'span class="capitalize"', $name );
			$parent_author_name = html( 'span class="capitalize"', $parent_author_name );
			$title = $this->subscribed_post_title_link;
		}

		if ( $this->parent_author and $this->parent_author->ID == $subscriber->ID ) {
			return sprintf( __( '%s replied to your comment on %s:', 'Postmatic' ), $name, $title );
		}

		if ( $this->parent_author ) {
			return sprintf(
				__( '%s left a reply to a comment by %s on %s:', 'Postmatic' ),
				$name,
				$parent_author_name,
				$this->subscribed_post_title_link
			);
		}

		return '';
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param $comment_id
	 * @return array
	 */
	protected function comment_children( $comment_id ) {
		$children = get_comments( array(
			'parent' => $comment_id,
			'status' => 'approve',
		) );

		if ( ! $children )
			return array();

		foreach ( $children as $child ) {
			$children = array_merge( $children, $this->comment_children( $child->comment_ID ) );
		}

		return $children;
	}

	/**
	 * Find recipients after flood control.
	 *
	 * @since 2.0.0
	 *
	 * @return array IDs of users who should receive a comment notification
	 */
	protected function flood_controlled_recipient_ids() {

		// We currently only mail standard WP comments
		if ( 'comment' != get_comment_type($this->prompt_comment->get_wp_comment() ) ) {
            return array();
        }

		$recipient_ids = $this->prompt_comment->get_recipient_ids();

		if ( ! $recipient_ids ) {

			$recipient_ids = $this->flood_controller->control_recipient_ids();
			/**
			 * Filter the recipient ids of notifications for a comment.
			 *
			 * @param array $recipient_ids
			 * @param WP_Post $post
			 */
			$recipient_ids = apply_filters( 'prompt/recipient_ids/comment', $recipient_ids, $this->prompt_comment );

			$this->prompt_comment->set_recipient_ids( $recipient_ids );
		}

		return $recipient_ids;
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param array $subscriber_ids
	 */
	protected function add_recipients( array $subscriber_ids ) {

		$this->set_individual_message_values( array() );

		foreach ( $subscriber_ids as $subscriber_id ) {

			$subscriber = get_userdata( $subscriber_id );

			if ( ! $subscriber or ! $subscriber->user_email )
				continue;

			$this->add_recipient( $subscriber );

		}
	}
}