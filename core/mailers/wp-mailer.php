<?php

class Prompt_Wp_Mailer extends Prompt_Mailer {

	/** @var  callable */
	protected $local_mailer;
	/** @var  Prompt_Handlebars */
	protected $handlebars;
	/** @var  int */
	protected $chunk;
	/** @var  array */
	protected $error_data;

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client|null $client
	 * @param callable|null $local_mailer
	 * @param int $chunk
	 */
	public function __construct(
		Prompt_Email_Batch $batch,
		Prompt_Interface_Http_Client $client = null,
		$local_mailer = null,
		$chunk = 0
	) {
		parent::__construct( $batch, $client );

		$this->chunk = $chunk;
		$this->handlebars = new Prompt_Handlebars();
		$this->local_mailer = $local_mailer ? $local_mailer : 'wp_mail';
		$this->error_data = array();
	}

	/**
	 * @since 2.0.0
	 *
	 * @return array|WP_Error
	 */
	public function send() {

		do_action( 'prompt/outbound/batch', $this->batch );

		if ( $this->is_retry and $this->already_retried() ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::DUPLICATE,
				__( 'Duplicate retry skipped.', 'Postmatic' ),
				array( 'batch' => $this->batch, 'retry_wait_seconds' => $this->retry_wait_seconds )
			);
		}

		$this->render_batch_template();

		$source_values = $this->batch->get_individual_message_values();
		$return_values = array();

		add_action( 'wp_mail_failed', array( $this, 'add_mail_error' ) );

		for( $i = 0; $i < count( $source_values ); $i += 1 ) {

			$local_email = $this->render_individual_email( $source_values[$i] );

			$return_values[$local_email['to_address']] = $this->send_prepared( $local_email );
		}
		
		remove_action( 'wp_mail_failed', array( $this, 'add_mail_error' ) );

		$this->log_errors();

		return $return_values;
	}

	/**
	 * Record a mail error.
	 * @since 2.0.0
	 * @param WP_Error $error
	 */
	public function add_mail_error( WP_Error $error ) {
		$this->error_data[] = array( 
			'message' => $error->get_error_message(), 
			'error_info' => $error->get_error_data() 
		);
	}
	
	/**
	 * Log local mail errors.
	 * @since 2.0.0
	 */
	protected function log_errors() {
		if ( empty( $this->error_data ) ) {
			return;
		}

		$notice = new Prompt_Admin_Local_Mail_Notice();
		$notice->undismiss();

		Prompt_Logging::add_error(
			'prompt_wp_mail',
			__( 'Replyable is having trouble sending email through your webhost. Consider upgrading to a paid Replyable account and we can send email for you.', 'Prompt_Core' ),
			array( 'error_data' => $this->error_data )
		);
	}
	
	
	/**
	 * Whether the current batch contains trackable reply requests.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function track_replies() {
		$track_replies = false;
		foreach( $this->batch->get_individual_message_values() as $values ) {
			if ( isset( $values['reply_to'] ) ) {
				$track_replies = true;
				break;
			}
		}
		return $track_replies;
	}

	/**
	 * Render the email content in the local HTML document template.
	 *
	 * @since 2.0.0
	 *
	 */
	protected function render_batch_template() {
		$template = $this->batch->get_batch_message_template();

		if ( strpos( $template['html_content'], '<html' ) !== false ) {
			// Already rendered
			return;
		}

		if ( isset( $template['html_content'] ) ) {
			$html_template = new Prompt_Template( 'html-local-email-wrapper.php' );
			$template['html_content'] = $html_template->render( $template );
		}

		if ( isset( $template['text_content'] ) ) {
			$text_template = new Prompt_Text_Template( 'text-email-wrapper.php' );
			$template['text_content'] = $text_template->render( $template );
		}

		$this->batch->set_batch_message_template( $template );
	}

	/**
	 * Render handlebars fields in the batch template using a set of individual message values.
	 *
	 * @since 2.0.0
	 *
	 * @param array $values
	 * @return array
	 */
	protected function render_individual_email( $values ) {

		$template = $this->batch->get_batch_message_template();

		$values = array_merge( $this->batch->get_default_values(), $values );

		$email_fields = array();

		$reply_to = isset( $values['reply_to'] ) ? $values['reply_to'] : null;

		if ( $reply_to ) {
			$email_fields['metadata'] = $reply_to['trackable-address'];
		}

		$values['ref_id'] = 'noreply';

		foreach( $template as $field_name => $field_template ) {
			$email_fields[$field_name] = $this->handlebars->render_string( $field_template, $values );
		}

		if ( isset( $template['reply_to'] ) and is_email( $template['reply_to'] ) ) {

			$email_fields['reply_address'] = Prompt_Email_Batch::address( $template['reply_to'] );
			$email_fields['reply_name'] = Prompt_Email_Batch::name( $template['reply_to'] );

		}

		return $email_fields;
	}

	/**
	 * Send a prepared and rendered email locally.
	 *
	 * @param array $email
	 * @return bool
	 */
	protected function send_prepared( array $email ) {

		if ( !is_email( $email['to_address'] ) ) {
			Prompt_Logging::add_error(
				Prompt_Enum_Error_Codes::OUTBOUND,
				__( 'Attempted to send to an invalid email address.', 'Postmatic' ),
				compact( 'email' )
			);
			return false;
		}

		$to = Prompt_Email_Batch::name_address( $email['to_address'], $email['to_name'] );
		$headers = array(
			'From: ' . Prompt_Email_Batch::name_address( $email['from_address'], $email['from_name'] ),
			'X-Postmatic-Site-URL: ' . home_url(),
		);

		if ( ! empty( $email['reply_address'] ) ) {
			$headers[] = 'Reply-To: ' . Prompt_Email_Batch::name_address( $email['reply_address'], $email['reply_name'] );
		}

		$unsubscribe_types = array( Prompt_Enum_Message_Types::COMMENT, Prompt_Enum_Message_Types::POST );

		if ( ! empty( $email['reply_address'] ) and in_array( $email['message_type'], $unsubscribe_types ) ) {
			$headers[] = 'List-Unsubscribe: <mailto:' . $email['reply_address'] . '?body=unsubscribe>';
		}

		$message = $email['text_content'];
		
		if ( !empty( $email['html_content'] ) ) {
			$headers[] = 'Content-Type: ' . Prompt_Enum_Content_Types::HTML . '; charset=UTF-8';
			$message = $email['html_content'];
		} 
		
		return call_user_func( $this->local_mailer, $to, $email['subject'], $message, $headers );
	}

	/**
	 * Submit emails to Postmatic for tracking addresses.
	 *
	 * @return object|WP_Error results
	 */
	protected function request_tracking_addresses() {

		$email_data = new stdClass();
		$email_data->actions = array( 'track-replies' );
		$email_data->outboundMessages = array();

		$message_values = $this->batch->get_individual_message_values();

		if ( empty( $message_values ) ) {
			return $email_data;
		}

		foreach ( $message_values as $value_set ) {
			$email_data->outboundMessages[] = $this->make_prompt_message( $value_set );
		}

		$response = $this->client->post_outbound_messages( $email_data );

		if ( $this->reschedule( $response ) ) {
			return $response;
		}

		$error = $this->translate_error( $response );

		if ( $error ) {
			return $error;
		}

		$results = json_decode( $response['body'] );

		if (
			! isset( $results->outboundMessages ) or
			count( $results->outboundMessages ) != count( $this->batch->get_individual_message_values() )
		) {
			return Prompt_Logging::add_error(
				'invalid_outbound_results',
				__( 'An email sending operation behaved erratically and may have failed.', 'Postmatic' ),
				compact( 'email_data', 'results' )
			);
		}

		return $results;
	}

	/**
	 * Format an email for the prompt outbound service.
	 *
	 * @param array $values
	 * @return array
	 */
	protected function make_prompt_message( array $values ) {

		$default_values = $this->batch->get_default_values();

		$values = array_merge( $default_values, $values );

		$template = $this->batch->get_batch_message_template();

		$message = array(
			'to' => array(
				'address' => $this->handlebars->render_string( $template['to_address'], $values ),
				'name' => $this->handlebars->render_string( $template['to_name'], $values ),
			),
			'from' => array(
				'address' => $this->handlebars->render_string( $template['from_address'], $values ),
				'name' => $this->handlebars->render_string( $template['from_name'], $values ),
			),
			'subject' => $this->handlebars->render_string( $template['subject'], $values ),
			'type' => $template['message_type'],
		);

		if ( isset( $values['reply_to'] ) ) {
			$message['metadata'] = $values['reply_to']['trackable-address'];
		}

		return $message;
	}

}