<?php

class Prompt_Comment_Flood_Controller {

	protected static $flood_meta_key = 'prompt_flood_comment';

	/** @var  object */
	protected $comment;
	/** @var  Prompt_Post */
	protected $prompt_post;
	/** @var  array */
	protected $trigger_count;

	function __construct( $comment ) {
		$this->comment = $comment;
		$this->prompt_post = new Prompt_Post( $comment->comment_post_ID );
		$this->trigger_count = Prompt_Core::$options->get( 'comment_flood_control_trigger_count' );
	}

	/**
	 * Trigger flood notification if necessary and return IDs that should
	 * receive a regular comment notification.
	 *
	 * @return array
	 */
	function control_recipient_ids() {
		$site_comments = new Prompt_Site_Comments();
		$site_recipient_ids = $site_comments->subscriber_ids();

		$comment_author_id = $this->comment->user_id;
		if ( !$comment_author_id ) {
			$author = get_user_by( 'email', $this->comment->comment_author_email );
			$comment_author_id = $author ? $author->ID : null;
		}

		$post_author_recipient_ids = array();
		if ( Prompt_Core::$options->get( 'auto_subscribe_authors' ) )
			$post_author_recipient_ids = array( $this->prompt_post->get_wp_post()->post_author );

		$post_recipient_ids =  array_diff( $this->prompt_post->subscriber_ids(), array( $comment_author_id ) );

		if ( $this->is_flood() ) {

			$this->prompt_post->set_flood_control_comment_id( $this->comment->comment_ID );

			$this->unsubscribe( $post_recipient_ids );

			$this->send_notifications( $post_recipient_ids );

			return $this->all_ids_except( $comment_author_id, $site_recipient_ids, $post_author_recipient_ids );
		}

		return $this->all_ids_except(
			$comment_author_id,
			$site_recipient_ids,
			$post_author_recipient_ids,
			$post_recipient_ids
		);
	}

	protected function all_ids_except( $exclude, $array1, $array2 = null, $_ = null ) {
		$args = func_get_args();

		$exclude_id = array_shift( $args );

		$all_ids = array_unique( call_user_func_array( 'array_merge', $args ) );

		return array_diff( $all_ids, array( $exclude_id ) );
	}

	/**
	 * @return bool
	 */
	protected function is_flood() {

		if ( get_comment_count( $this->prompt_post->id() ) < $this->trigger_count )
			return false;

		if ( $this->prompt_post->get_flood_control_comment_id() )
			return false;

		$last_hour_comment_count = get_comments( array(
			'count' => true,
			'post_id' => $this->prompt_post->id(),
			'status' => 'approve',
			'date_query' => array(
				array(
					'column' => 'comment_date',
					'after' => '14 hours ago',
				)
			)
		) );

		if ( $last_hour_comment_count <= $this->trigger_count )
			return false;

		return true;
	}

	/**
	 * Unsubscribe an array of user IDs from the post.
	 * @param $ids
	 */
	protected function unsubscribe( $ids ) {
		foreach( $ids as $id ) {
			$this->prompt_post->unsubscribe( $id );
		}
	}

	protected function send_notifications( $recipient_ids ) {

		$template_data = array(
			'post' => $this->prompt_post,
			'comment_header' => true,
			'is_api_delivery' => Prompt_Core::is_api_transport(),
		);
		/**
		 * Filter comment email template data.
		 *
		 * @param array $template_data {
		 * @type Prompt_post $post
		 * @type bool $comment_header
		 * }
		 */
		$template_data = apply_filters( 'prompt/comment_flood_email/template_data', $template_data );

		$html_template = new Prompt_Template( 'comment-flood-email.php' );
		$text_template = new Prompt_Text_Template( 'comment-flood-email-text.php' );

		$footnote_html = sprintf(
			__( 'You received this email because you\'re subscribed to %s.', 'Postmatic' ),
			$this->prompt_post->subscription_object_label()
		);

		$batch = new Prompt_Email_Batch( array(
			'subject' => __( 'We\'re pausing comment notices for you.', 'Postmatic' ),
			'text_content' => $text_template->render( $template_data ),
			'html_content' => $html_template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
			'reply_to' => '{{{reply_to}}}',
			'footnote_html' => $footnote_html,
			'footnote_text' => Prompt_Content_Handling::reduce_html_to_utf8( $footnote_html ),
		) );

		foreach( $recipient_ids as $recipient_id ) {

			$subscriber = get_userdata( $recipient_id );

			if ( !$subscriber or !$subscriber->user_email ) {
				continue;
			}

			$reply_to = 'donotreply@gopostmatic.com';

			if ( Prompt_Core::is_api_transport() ) {
				$command = new Prompt_Comment_Flood_Command();
				$command->set_post_id( $this->prompt_post->id() );
				$command->set_user_id( $recipient_id );
				$reply_to = Prompt_Email_Batch::trackable_address(
					Prompt_Command_Handling::get_command_metadata( $command )
				);
			}

			$batch->add_individual_message_values( array(
				'to_address' => $subscriber->user_email,
				'reply_to' => $reply_to,
			) );

		}

		/**
		 * Filter comment notification email batch.
		 *
		 * @param Prompt_Email_Batch $batch
		 * @param array $template_data see prompt/comment_email/template_data
		 */
		$batch = apply_filters( 'prompt/comment_flood_email_batch', $batch, $template_data );

		Prompt_Factory::make_mailer( $batch )->send();

	}

}