<?php

class Prompt_Admin_Zero_Spam_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var string override the option key */
	protected $skip_option_key = 'skip_zero_spam_intro';

	/**
	 * Render a message if the zero spam plugin isn't active
	 *
	 * If it is active, dismiss and return an empty string.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function render() {

		if ( is_plugin_active( 'zero-spam/zero-spam.php' ) or ! current_user_can( 'update_plugins' ) ) {
			return '';
		}

		return $this->render_message(
			sprintf(
				__(
					'Did you know there is an excellent and free way to keep spam comments from ever getting submitted? We heartily recommend installing <a href="%s">WPBruiser</a>.',
					'Postmatic'
				),
				'https://wordpress.org/plugins/goodbye-captcha/'
			)
		);
	}

}
