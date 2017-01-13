<?php

class Prompt_Admin_Local_Mail_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var string override the option key */
	protected $skip_option_key = 'skip_local_mail_intro';
	/** @var bool */
	protected $detected_failure = false;

	/**
	 * Render a message if local mailing doesn't appear to work.
	 *
	 * If it does work, dismiss and return an empty string.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function render() {

		if ( Prompt_Enum_Email_Transports::LOCAL != Prompt_Core::$options->get( 'email_transport' ) ) {
			return '';
		}

		if ( !current_user_can( 'manage_options' ) ) {
			return '';
		}

		add_action( 'wp_mail_failed', array( $this, 'detect_failure' ) );

		$mail_result = wp_mail(
			'Local Test <null@email.gopostmatic.com>',
			'Check wp_mail() on ' . get_option( 'blogname' ),
			'This is just a test that no one will read.'
		);

		remove_action( 'wp_mail_failed', array( $this, 'detect_failure' ) );

		if ( ! $this->detected_failure and $mail_result ) {
			$this->dismiss();
			return '';
		}

		return $this->render_message(
			__(
				'Heads up! We detected that your hosting account is unable to send email. You\'ll have to contact them for help, or use the button below to upgrade Replyable. We\'ll send email for you from our servers.',
				'Postmatic'
			)
		);
	}

	/**
	 * @since 2.0.11
	 * @param WP_Error $error
	 */
	protected function detect_failure( $error ) {
		$this->detected_failure = true;
	}
}
