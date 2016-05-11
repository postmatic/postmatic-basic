<?php

class Prompt_Admin_Local_Mail_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var string override the option key */
	protected $skip_option_key = 'skip_local_mail_intro';

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

		$mail_result = wp_mail(
			'null@email.gopostmatic.com',
			'Check wp_mail() on ' . get_option( 'blogname' ),
			'This is just a test that no one will read.'
		);

		if ( $mail_result ) {
			$this->dismiss();
			return '';
		}

		return $this->render_message(
			__(
				'We detected that your host is unable to send email. You\'ll have to contact them for help, or upgrade Postmatic to use our awesome delivery services.',
				'Postmatic'
			)
		);
	}

}
