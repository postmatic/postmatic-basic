<?php

class Prompt_Subscription_Mailing {

	/**
	 * Send an email to verify a subscription from a non-existent user.
	 *
	 * @param Prompt_Interface_Subscribable[]|Prompt_Interface_Subscribable $lists
	 * @param string                                                        $email_address
	 * @param array                                                         $user_data
	 * @param Prompt_Register_Subscribe_Command                             $resend_command
	 * @param int                                                           $retry_wait_seconds
	 */
	public static function send_agreement(
		$lists,
		$email_address,
		$user_data,
		$resend_command = null,
		$retry_wait_seconds = null
	) {

		$user_data['user_email'] = $email_address;

		$batch = new Prompt_Subscription_Agreement_Email_Batch( $lists );

		$batch->add_agreement_recipient( $user_data, $resend_command );

		Prompt_Factory::make_mailer( $batch )->set_retry_wait_seconds( $retry_wait_seconds )->send();
	}

	/**
	 * Send emails to verify subscriptions from non-existent users.
	 *
	 * Try to be idempotent, so only the first of repeated calls sends mail.
	 *
	 * @param Prompt_Interface_Subscribable[]|Prompt_Interface_Subscribable $lists
	 * @param array                                                         $users_data         An array of user_data
	 *                                                                                          arrays that include the
	 *                                                                                          user_email field.
	 * @param array                                                         $template_data      An array of data to
	 *                                                                                          provide to the
	 *                                                                                          subscription agreement
	 *                                                                                          template.
	 * @param int                                                           $chunk
	 * @param int                                                           $retry_wait_seconds Minimum time to wait if
	 *                                                                                          a retry is necessary,
	 *                                                                                          or null to disable
	 *                                                                                          retry
	 */
	public static function send_agreements(
		$lists,
		$users_data,
		$template_data = array(),
		$chunk = 0,
		$retry_wait_seconds = null
	) {

		$batch = new Prompt_Subscription_Agreement_Email_Batch( $lists, $template_data );

		$batch->add_agreement_recipients( $users_data );

		Prompt_Factory::make_mailer( $batch, null, $chunk )->set_retry_wait_seconds( $retry_wait_seconds )->send();
	}

	/**
	 * Send agreements that have been cached with a key.
	 *
	 * @param string $key
	 */
	public static function send_cached_invites( $key ) {

		$agreements = get_option( $key );

		if ( !$agreements ) {
			return;
		}

		delete_option( $key );

		call_user_func_array( array( __CLASS__, 'send_agreements' ), $agreements );
	}

	/**
	 * Schedule a batch of agreements to be sent.
	 *
	 * If the API email transport is in use, agreements are sent inline. Local mailing triggers
	 * a new process.
	 *
	 * @since 1.3.0
	 *
	 * @param Prompt_Interface_Subscribable[]|Prompt_Interface_Subscribable $lists
	 * @param array                                                         $users_data    An array of user_data arrays
	 *                                                                                     that include the user_email
	 *                                                                                     field.
	 * @param array                                                         $template_data An array of data to provide
	 *                                                                                     to the subscription
	 *                                                                                     agreement template.
	 */
	public static function schedule_agreements( $lists, $users_data, $template_data = array() ) {

		if ( Prompt_Core::$options->get( 'email_transport' ) === Prompt_Enum_Email_Transports::API ) {
			// inline
			self::send_agreements( $lists, $users_data, $template_data );
			return;
		}

		$batch = new Prompt_Subscription_Agreement_Email_Batch( $lists, $template_data );

		$batch->add_agreement_recipients( $users_data );

		$mailer = new Prompt_Subscription_Agreement_Wp_Mailer( $batch );

		$mailer = apply_filters( 'prompt/subscription_mailing/schedule_wp_mailer', $mailer );

		// new process
		$mailer->schedule();
	}

	/**
	 * Send an unsubscription confirmation email.
	 *
	 * @param int                           $subscriber_id
	 * @param Prompt_Interface_Subscribable $object
	 */
	public static function send_unsubscription_notification( $subscriber_id, $object ) {
		self::send_subscription_notification( $subscriber_id, $object, true );
	}

	/**
	 * Send a subscription confirmation email.
	 *
	 * @param int                           $subscriber
	 * @param Prompt_Interface_Subscribable $object
	 * @param boolean                       $un True if unsubscribing, default false.
	 */
	public static function send_subscription_notification( $subscriber, $object, $un = false ) {

		$prompt_subscriber = new Prompt_User( $subscriber );
		$subscriber = $prompt_subscriber->get_wp_user();

		if ( !$subscriber or !is_email( $subscriber->user_email ) ) {
			Prompt_Logging::add_error(
				'invalid_subscriber',
				__( 'Tried to notify an invalid subscriber.', 'Postmatic' ),
				compact( 'subscriber', 'object', 'un' )
			);
			return;
		}

		if ( $un ) {

			$subject = sprintf(
				__( 'You\'re unsubscribed from %s', 'Postmatic' ),
				$object->subscription_object_label()
			);
			$footnote_html = $footnote_text = '';
			$template_file = "unsubscribed-email.php";
			$filter = 'prompt/unsubscribed_email';
			$comments = array();

		} else {

			$subject = sprintf(
				__( 'You\'re subscribed to %s', 'Postmatic' ),
				$object->subscription_object_label()
			);

			list( $footnote_html, $footnote_text ) = self::welcome_footnote_content( $object );

			$template_file = "subscribed-email.php";
			$filter = 'prompt/subscribed_email';
			$comments = self::comments( $object );
		}

		$html_template = new Prompt_Template( $template_file );
		$text_template = new Prompt_Text_Template( str_replace( '.php', '-text.php', $template_file ) );

		$template_data = array(
			'subscriber' => $prompt_subscriber->get_wp_user(),
			'object' => $object,
			'subscribed_introduction' => self::introduction( $object, $un ),
			'comments' => $comments,
		);
		/**
		 * Filter template data for subscription notification email.
		 *
		 * @param array                        $template_data           {
		 *                                                              Data supplied to the subscription notification email template.
		 *
		 * @type WP_User                       $object                  The object subscribed to
		 * @type Prompt_Interface_Subscribable $object                  The object subscribed to
		 * @type string                        $subscribed_introduction Custom introductory content.
		 * @type array                         $comments                For post subscriptions, the comments on the post so far.
		 * }
		 */
		$template_data = apply_filters( $filter . '/template_data', $template_data );

		$post_id = ( $object instanceof Prompt_Post ) ? $object->id() : 0;

		$reply_to = 'donotreply@gopostmatic.com';

		if ( Prompt_Core::is_api_transport() ) {
			$command = new Prompt_Confirmation_Command();
			$command->set_post_id( $post_id );
			$command->set_user_id( $subscriber->ID );
			$command->set_object_type( get_class( $object ) );
			$command->set_object_id( $object->id() );
			$reply_to = Prompt_Email_Batch::trackable_address(
				Prompt_Command_Handling::get_command_metadata( $command )
			);
		}

		$batch_data = array(
			'to_name' => $subscriber->display_name,
			'to_address' => $subscriber->user_email,
			'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
			'subject' => $subject,
			'html_content' => $html_template->render( $template_data ),
			'text_content' => $text_template->render( $template_data ),
			'reply_to' => $reply_to,
			'welcome_message' => Prompt_Core::$options->get( 'subscriber_welcome_message' ),
			'footnote_html' => $footnote_html,
			'footnote_text' => $footnote_text,
		);

		if ( $comments and comments_open( $post_id ) ) {
			$batch_data = array_merge(
				$batch_data,
				Prompt_Command_Handling::get_comment_reply_macros( $comments, $subscriber->ID )
			);
		}

		$batch = Prompt_Email_Batch::make_for_single_recipient( $batch_data );

		/**
		 * Filter subscription notification email batch.
		 *
		 * @param Prompt_Email_Batch $batch
		 * @param array              $template_data @see prompt/subscribed_email/template_data
		 */
		$batch = apply_filters( 'prompt/subscribed_batch', $batch, $template_data );

		Prompt_Factory::make_mailer( $batch )->send();
	}

	/**
	 * Send a rejoin confirmation email.
	 *
	 * @param int         $subscriber
	 * @param Prompt_Post $prompt_post
	 */
	public static function send_rejoin_notification( $subscriber, $prompt_post ) {

		$prompt_subscriber = new Prompt_User( $subscriber );
		$subscriber = $prompt_subscriber->get_wp_user();

		$comments = self::comments( $prompt_post );

		$html_template = new Prompt_Template( 'rejoined-email.php' );
		$text_template = new Prompt_Text_Template( 'rejoined-email-text.php' );

		$template_data = array(
			'object' => $prompt_post,
			'comments' => $comments,
		);
		/**
		 * Filter template data for rejoin notification email.
		 *
		 * @param array                        $template_data {
		 *                                                    Data supplied to the subscription notification email template.
		 *
		 * @type Prompt_Interface_Subscribable $prompt_post   The object subscribed to
		 * @type array                         $comments      The comments since flood control was triggered.
		 * }
		 */
		$template_data = apply_filters( 'prompt/rejoined_email/template_data', $template_data );

		$footnote_html = sprintf(
			__( 'You received this email because you\'re subscribed to %s.', 'Postmatic' ),
			$prompt_post->subscription_object_label()
		);

		$reply_to = 'donotreply@gopostmatic.com';
		if ( Prompt_Core::is_api_transport() ) {
			$reply_to = Prompt_Email_Batch::trackable_address(
				Prompt_Command_Handling::get_comment_command_metadata( $subscriber->ID, $prompt_post->id() )
			);
		}

		$batch_data = array(
			'to_address' => $subscriber->user_email,
			'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
			'subject' => sprintf( __( 'You\'ve rejoined %s', 'Postmatic' ), $prompt_post->subscription_object_label() ),
			'html_content' => $html_template->render( $template_data ),
			'text_content' => $text_template->render( $template_data ),
			'reply_to' => $reply_to,
			'welcome_back_message' => sprintf(
				__( 'Welcome back, <span class="capitalize">%s</span>.', 'Postmatic' ),
				$subscriber->display_name
			),
			'footnote_html' => $footnote_html,
			'footnote_text' => Prompt_Content_Handling::reduce_html_to_utf8( $footnote_html ),
		);

		if ( comments_open( $prompt_post->id() ) ) {
			$batch_data = array_merge(
				$batch_data,
				Prompt_Command_Handling::get_comment_reply_macros( $comments, $subscriber->ID )
			);
		}

		$batch = Prompt_Email_Batch::make_for_single_recipient( $batch_data );

		/**
		 * Filter subscription notification email batch.
		 *
		 * @param Prompt_Email_Batch $batch
		 * @param array              $template_data @see prompt/rejoined_email/template_data
		 */
		$batch = apply_filters( 'prompt/rejoined_batch', $batch, $template_data );

		Prompt_Factory::make_mailer( $batch )->send();
	}

	/**
	 * @since 1.0.0
	 * @param Prompt_Interface_Subscribable $object
	 * @return array
	 */
	protected static function comments( Prompt_Interface_Subscribable $object ) {

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) )
			return array();

		if ( !is_a( $object, 'Prompt_Post' ) )
			return array();

		return get_comments( array(
			'post_id' => $object->id(),
			'status' => 'approve',
			'order' => 'ASC',
		) );
	}

	/**
	 * Assemble subscription management content appropriate for the $list and current settings.
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Interface_Subscribable $list
	 * @return array HTML followed by text content
	 */
	protected static function welcome_footnote_content( Prompt_Interface_Subscribable $list ) {

		$html_parts = array();
		$text_parts = array();

		/**
		 * Filter extra footnote content for welcome emails.
		 *
		 * @param array $content Two element array containing first HTML then text content.
		 * @param Prompt_Interface_Subscribable The list that was subscribed to.
		 */
		list( $html_parts[], $text_parts[] ) = apply_filters(
			'prompt/subscription_mailing/extra_welcome_footnote_content',
			array( '', '' ),
			$list
		);

		if ( Prompt_Core::is_api_transport() ) {

			$unsubscribe_mailto = sprintf(
				'mailto:{{{reply_to}}}?subject=%s&body=%s',
				rawurlencode( __( 'Press send to confirm', 'Postmatic' ) ),
				rawurlencode( Prompt_Unsubscribe_Matcher::target() )
			);
			$unsubscribe_format = __( 'To unsubscribe at any time reply with the word \'%s\'.', 'Postmatic' );

			$html_parts[] = sprintf(
				$unsubscribe_format,
				"<a href=\"$unsubscribe_mailto\">" . Prompt_Unsubscribe_Matcher::target() . '</a>'
			);
			$text_parts[] = sprintf( $unsubscribe_format, Prompt_Unsubscribe_Matcher::target() );
		}

		return array( implode( ' ', $html_parts ), implode( ' ', $text_parts ) );
	}

	/**
	 * @since 2.0.0
	 * @param Prompt_Interface_Subscribable $list
	 * @param bool $unsubscribing
	 * @return string
	 */
	protected static function introduction( Prompt_Interface_Subscribable $list, $unsubscribing ) {

		if ( $unsubscribing or $list instanceof Prompt_Post ) {
			return '';
		}

		return apply_filters( 'the_content', Prompt_Core::$options->get( 'subscribed_introduction' ) );
	}

}