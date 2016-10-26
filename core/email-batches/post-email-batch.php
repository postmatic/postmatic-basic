<?php

/**
 * An email batch that knows how to render a post for emails.
 */
class Prompt_Post_Email_Batch extends Prompt_Email_Batch {

	/** @var  Prompt_Post_Rendering_Context */
	protected $context;
	/** @var bool */
	protected $replyable;
	/** @var array */
	protected $comments;

	/**
	 * Builds an email without specific recipient info that can be used as a template for all recipients.
	 *
	 * The set_recipient method can be called to fill in recipient-specific fields.
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Post_Rendering_Context $context      Rendering context for the target post
	 * @param array                         $args         {
	 * @type bool                           $excerpt_only Override the excerpt only checkbox in the delivery metabox.
	 *                                                    }
	 */
	public function __construct( Prompt_Post_Rendering_Context $context, $args = array() ) {

		$this->context = $context;

		$context->setup();

		$prompt_author = $context->get_author();
		$prompt_post = $context->get_post();

		$subject = html_entity_decode( get_the_title(), ENT_QUOTES );

		list( $footnote_html, $footnote_text ) = $this->footnote_content();

		if ( 'draft' == $prompt_post->get_wp_post()->post_status ) {
			/* translators: %s is a post title */
			$subject = sprintf( __( 'PREVIEW of %s', 'Postmatic' ), $subject );
			$footnote_html = $footnote_text = '';
		}

		$excerpt_only = Prompt_Admin_Delivery_Metabox::excerpt_only( $prompt_post->id() );
		if ( isset( $args['excerpt_only'] ) ) {
			$excerpt_only = $args['excerpt_only'];
		}

		$this->comments = get_approved_comments( $prompt_post->id() );

		$by = '';
		if ( apply_filters( 'prompt/new_post_email/include_author', false ) ) {
			/* translators: %s is the post author name */
			$by = sprintf( __( 'by %s', 'Postmatic' ), get_the_author() );
		}

		$template_data = array(
			'prompt_author' => $prompt_author,
			'prompt_post' => $prompt_post,
			'excerpt_only' => $excerpt_only,
			'by' => $by,
			'the_text_content' => $context->get_the_text_content(),
			'subject' => $subject,
			'after_title_content' => $context->alternate_versions_menu(),
			'after_post_content' => $context->has_fancy_content() ? '<hr/>' : '',
			'comments' => $this->comments,
		);
		/**
		 * Filter new post email template data.
		 *
		 * @param array      $template_data      {
		 * @type Prompt_User $prompt_author
		 * @type Prompt_Post $prompt_post
		 * @type bool        $excerpt_only       whether to include only the post excerpt
		 * @type string      $by                 author credit if enabled
		 * @type string      $the_text_content
		 * @type string      $subject
		 * @type string      $after_title_content
		 * @type string      $after_post_content
		 * @type array       $comments
		 *                                       }
		 * @param Prompt_Post_Rendering_Context  $context
		 */
		$template_data = apply_filters( 'prompt/post_email_batch/template_data', $template_data, $context );

		$html_template = new Prompt_Template( "new-post-email.php" );
		$text_template = new Prompt_Text_Template( "new-post-email-text.php" );

		$batch_message_template = array(
			'subject' => $subject,
			'from_name' => '{{{from_name}}}',
			'text_content' => $text_template->render( $template_data ),
			'html_content' => $html_template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::POST,
			'reply_to' => '{{{reply_to}}}',
			'footnote_html' => $footnote_html,
			'footnote_text' => $footnote_text,
		);

		$this->replyable = ( comments_open( $prompt_post->id() ) and !$excerpt_only );

		$default_values = array(
			'from_name' => $this->to_utf8( get_option( 'blogname' ) ),
		);

		$context->reset();

		parent::__construct( $batch_message_template, array(), $default_values );
	}

	/**
	 * Add recipients who have not already been sent a notice for this post.
	 *
	 * @since 2.0.0
	 *
	 * @return $this
	 */
	public function add_unsent_recipients() {

		$recipient_ids = $this->context->get_post()->unsent_recipient_ids();

		foreach ( $recipient_ids as $recipient_id ) {
			$this->add_recipient( new Prompt_User( $recipient_id ) );
		}

		return $this;
	}

	/**
	 * Record current recipients so they are not sent another notice for this post.
	 *
	 * @since 2.0.0
	 *
	 * @return $this;
	 */
	public function lock_for_sending() {

		$recipient_ids = wp_list_pluck( $this->individual_message_values, 'id' );

		$this->context->get_post()->add_sent_recipient_ids( $recipient_ids );

		return $this;
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

		$this->context->get_post()->remove_sent_recipient_ids( $ids );

		return $this;
	}

	/**
	 * Record user IDs for a list of addresses that failed to send.
	 *
	 * @since 2.0.14
	 *
	 * @param array $addresses
	 * @return $this;
	 */
	public function record_failures( $addresses ) {

		$ids = array_map( array( $this, 'to_address_to_id' ), $addresses );

		$this->context->get_post()->add_failed_recipient_ids( $ids );

		return $this;
	}

	/**
	 * Record a temporary failure fot current recipients so they will still be sent a notice for this post on retry.
	 *
	 * @since 2.0.0
	 *
	 * @return $this;
	 */
	public function clear_for_retry() {

		$recipient_ids = wp_list_pluck( $this->individual_message_values, 'id' );

		$this->context->get_post()->remove_sent_recipient_ids( $recipient_ids );

		return $this;
	}


	/**
	 * Add recipient-specific values to the batch.
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_User $recipient
	 * @return $this
	 */
	public function add_recipient( Prompt_User $recipient ) {

		if ( !$recipient->get_wp_user() ) {
			trigger_error( __( 'Did not add an invalid post recipient', 'Postmatic' ), E_USER_NOTICE );
			return $this;
		}

		$prompt_site = $this->context->get_site();
		$prompt_author = $this->context->get_author();

		$subscribed_object = $prompt_author->is_subscribed( $recipient->id() ) ? $prompt_author : $prompt_site;
		$unsubscribe_link = new Prompt_Unsubscribe_Link( $recipient->get_wp_user() );

		$values = array(
			'id' => $recipient->id(),
			'to_name' => $recipient->get_wp_user()->display_name,
			'to_address' => $recipient->get_wp_user()->user_email,
			'subscribed_object_label' => html_entity_decode( $subscribed_object->subscription_object_label() ),
			'unsubscribe_url' => $unsubscribe_link->url(),
		);

		if ( is_a( $subscribed_object, 'Prompt_User' ) and $prompt_author->id() ) {
			$values['from_name'] = get_option( 'blogname' ) . ' [' . $prompt_author->get_wp_user()->display_name . ']';
		}

		$values = array_merge( $values, $this->mail_command_values( $recipient->id(), $subscribed_object ) );

		return $this->add_individual_message_values( $values );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return Prompt_Post_Rendering_Context
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * If replyable returns all comment reply command macros, otherwise a command macro to forward replies to
	 * the post author.
	 *
	 * @since 2.0.0
	 *
	 * @param int                           $recipient_id
	 * @param Prompt_Interface_Subscribable $subscribed_list
	 * @return array
	 */
	protected function mail_command_values( $recipient_id, Prompt_Interface_Subscribable $subscribed_list ) {

		$values = array();

		if ( $this->replyable ) {

			$command = new Prompt_New_Post_Comment_Command();
			$command->set_post_id( $this->context->get_post()->id() );
			$command->set_user_id( $recipient_id );

			$values = Prompt_Command_Handling::get_comment_reply_macros( $this->comments, $recipient_id );

		} else {

			$command = new Prompt_Forward_Command();
			$command->set_from_user_id( $recipient_id );
			$command->set_to_user_id( $this->context->get_post()->get_wp_post()->post_author );
			$command->set_subscription_object( $subscribed_list );

		}

		$values['reply_to'] = $this->trackable_address( Prompt_Command_Handling::get_command_metadata( $command ) );

		return $values;
	}

	/**
	 * @since 2.0.0
	 *
	 * @return array Two strings, HTML then text
	 */
	protected function footnote_content() {
		$html_parts = array();
		$text_parts = array();

		/* translators: %s is a subscription list title */
		$why = sprintf(
			__( 'You received this email because you\'re subscribed to %s.', 'Postmatic' ),
			'{{{subscribed_object_label}}}'
		);
		$html_parts[] = $why;
		$text_parts[] = $why;

		/**
		 * Filter extra footnote content for post emails.
		 *
		 * @param array $content Two element array containing first HTML then text content.
		 */
		list( $html_parts[], $text_parts[] ) = apply_filters(
			'prompt/post_email_batch/extra_footnote_content',
			array( '', '' )
		);

		/* translators: %s is the unsubscribe command word */
		$unsub_format = __( 'To unsubscribe reply with the word \'%s\'.', 'Postmatic' );

		$html_parts[] = sprintf(
			$unsub_format,
			"<a href=\"{$this->unsubscribe_mailto()}\">" . Prompt_Unsubscribe_Matcher::target() . '</a>'
		);
		$text_parts[] = sprintf( $unsub_format, Prompt_Unsubscribe_Matcher::target() );

		$html_parts = array( html( 'p', implode( ' ', $html_parts ) ) );
		$text_parts[] = "\n\n";

		if ( $this->replyable ) {
			$sub_mailto = sprintf(
				'mailto:{{{reply_to}}}?subject=%s&body=%s',
				rawurlencode( __( 'Subscribe to comments', 'Postmatic' ) ),
				rawurlencode( Prompt_Subscribe_Matcher::target() )
			);
			/* translators: %s is the subscribed command word */
			$sub_format = __(
				'To keep up to date with the conversation you can subscribe to comments. Just reply to this email with the word \'%s\'.',
				'Postmatic'
			);
			$html_parts[] = html( 'p',
				sprintf(
					$sub_format,
					"<a href=\"{$sub_mailto}\">" . Prompt_Subscribe_Matcher::target() . '</a>'
				)
			);
			$text_parts[] = sprintf( $sub_format, Prompt_Subscribe_Matcher::target() );
		}

		return array( implode( ' ', $html_parts ), implode( ' ', $text_parts ) );
	}
}