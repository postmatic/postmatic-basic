<?php

/**
 * An email batch that knows how to render subscription agreements.
 *
 * @since 2.0.0
 */
class Prompt_Subscription_Agreement_Email_Batch extends Prompt_Email_Batch {

	/** @var Prompt_Interface_Subscribable[] */
	protected $lists;
	/** @var array */
	protected $message_data;
	/** @var  array */
	protected $users_data;

	/**
	 * Builds an email batch with content based on a subscribable object.
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Interface_Subscribable[]|Prompt_Interface_Subscribable $lists
	 * @param array $message_data
	 */
	public function __construct( $lists, $message_data = array() ) {

		$this->lists = is_array( $lists ) ? $lists : array( $lists );
		$this->users_data = array();
		$this->message_data = $message_data;

		$message_data = array_merge( array( 'lists' => $this->lists ), $message_data );
		/**
		 * Filter new user subscription verification email template data.
		 * @param array $message_data {
		 * @type Prompt_Interface_Subscribable $lists The object being subscribed to
		 * }
		 */
		$message_data = apply_filters( 'prompt/subscription_agreement_email_batch/template_data', $message_data );

		$html_template = new Prompt_Template( 'subscription-agreement-email.php' );
		$text_template = new Prompt_Text_Template( 'subscription-agreement-email-text.php' );

		$subject = sprintf(
			__( '{{name_prefix}}Important: Please confirm your subscription to %s', 'Postmatic' ),
			$this->lists[0]->subscription_object_label()
		);
		if ( count( $this->lists ) > 1 ) {
			$subject = __( '{{name_prefix}}Important: Please select your subscription', 'Postmatic' );
		}

		$is_api_transport = Prompt_Core::$options->is_api_transport();
		$message_data['is_api_transport'] = $is_api_transport;

		$batch_message_template = array(
			'subject' => $subject,
			'from_name' => get_option( 'blogname' ),
			'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
			'html_content' => $html_template->render( $message_data ),
			'text_content' => $text_template->render( $message_data ),
			'reply_to' => $is_api_transport ? '{{{reply_to}}}' : 'donotreply@gopostmatic.com',
		);

		// Override template with message data
		foreach ( $batch_message_template as $name => $value ) {
			if ( isset( $message_data[$name] ) ) {
				$batch_message_template[$name] = $message_data[$name];
			}
		}

		$batch_message_template['footnote_html'] = $this->footnote_html( $batch_message_template['message_type'] );
		$batch_message_template['footnote_text'] = $this->footnote_text( $batch_message_template['message_type'] );

		parent::__construct( $batch_message_template );
	}

	/**
	 * Add individual message values based on user data and an optional resend command.
	 *
	 * Sets to_name, to_address, from_name, and recipient_values.
	 *
	 * @since 2.0.0
	 *
	 * @param array $user_data
	 * @param Prompt_Interface_Command $resend_command
	 * @return $this|WP_Error
	 */
	public function add_agreement_recipient( array $user_data, Prompt_Interface_Command $resend_command = null ) {
		$this->users_data[] = $user_data;

		$command = $resend_command;
		$notice_html = $this->resend_notice_html();
		$notice_text = $this->resend_notice_text();
		$email_address = $user_data['user_email'];
		
		if ( !is_email( $email_address ) ) {
			return Prompt_Logging::add_error(
				'invalid_agreement_email',
				__( 'Failed to add an agreement recipient with an invalid email address.', 'Postmatic' ),
				compact( $user_data )
			);
		}
		
		if ( !$resend_command ) {
			$command = new Prompt_Register_Subscribe_Command();
			$notice_html = '';
			$notice_text = '';
			$saved = $command->save_subscription_data( $this->lists, $email_address, $user_data );
			if ( is_wp_error( $saved ) ) {
				return Prompt_Logging::add_wp_error( $saved );
			}
		}

		$values = array(
			'to_address' => $email_address,
			'opt_in_url' => Prompt_Routing::opt_in_url( $command ),
			'notice_html' => $notice_html,
			'notice_text' => $notice_text,
		);

		if ( isset( $user_data['display_name'] ) ) {
			$values['to_name'] = $user_data['display_name'];
		}

		if ( ! empty( $user_data['display_name'] ) ) {
			$values['name_prefix'] = $user_data['display_name'] . ' - ';
		}

		if ( Prompt_Core::is_api_transport() ) {
			$values['reply_to'] = array( 'trackable-address' => Prompt_Command_Handling::get_command_metadata( $command ) );
		}

		$this->add_individual_message_values( $values );

		return $this;
	}

	/**
	 * Add multiple message recipients based on users data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $users_data
	 */
	public function add_agreement_recipients( array $users_data ) {
		foreach ( $users_data as $user_data ) {
			$this->add_agreement_recipient( $user_data );
		}
	}

	/**
	 * @return Prompt_Interface_Subscribable
	 */
	public function get_lists() {
		return $this->lists;
	}

	/**
	 * @return array
	 */
	public function get_message_data() {
		return $this->message_data;
	}

	/**
	 * @return array
	 */
	public function get_users_data() {
		return $this->users_data;
	}

	/**
	 * @return string
	 */
	protected function resend_notice_html() {
		return html( 'h3', __( 'Important Notice', 'Postmatic' ) ) .
		html( 'p',
			sprintf(
				__(
					'You recently signed up for updates from %s. We sent you an email asking for verification but you did not reply correctly. Please read the following:',
					'Postmatic'
				),
				get_bloginfo( 'name' )
			)
		);
	}

	/**
	 * @return string
	 */
	protected function resend_notice_text() {
		return Prompt_Html_To_Markdown::convert( $this->resend_notice_html() );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $message_type
	 * @return string
	 */
	protected function footnote_html( $message_type ) {
		if ( Prompt_Enum_Message_Types::INVITATION == $message_type ) {
			/* translators: placeholders are the URL and name of the site */
			return sprintf(
				__( 'This is invitation was sent to you from <a href="%s">%s</a>. If you are not interested in subscribing you can safely ignore it. We will not email you again.', 'Postmatic' ),
				home_url(),
				get_option( 'blogname' )
			);
		}

		/* translators: placeholders are the URL and name of the site */
		return sprintf(
			__(
				'You received this email because you requested a subscription from <a href="%s">%s</a>.',
				'Postmatic'
			),
			home_url(),
			get_option( 'blogname' )
		);
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $message_type
	 * @return string
	 */
	protected function footnote_text( $message_type ) {
		if ( Prompt_Enum_Message_Types::INVITATION == $message_type ) {
			/* translators: placeholders are the name and URL of the site */
			return sprintf(
				__( 'This invitation was sent to you on behalf of %s at %s.', 'Postmatic' ),
				get_option( 'blogname' ),
				home_url()
			);
		}

		/* translators: placeholders are the name and URL of the site */
		return sprintf(
			__(
				'You received this email because you requested a subscription from %s at %s.',
				'Postmatic'
			),
			get_option( 'blogname' ),
			home_url()
		);
	}
}
