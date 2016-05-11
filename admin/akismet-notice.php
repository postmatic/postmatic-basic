<?php

class Prompt_Admin_Akismet_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var string override the option key */
	protected $skip_option_key = 'skip_akismet_intro';

	/**
	 * Render a message if Akismet isn't active.
	 *
	 * If it is, return an empty string.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function render() {

		if ( is_plugin_active( 'akismet/akismet.php' ) )
			return '';

		return $this->render_message(
			sprintf(
				__(
					'Heads up! We noticed Akismet is not active on your site. Akismet is free, bundled with WordPress, and stops the vast majority of comment spam. Please be sure that you are using it or a similar product to keep from spamming your subscribers. <a href="%s" target="_blank">Learn more</a>.',
					'Postmatic'
				),
				Prompt_Enum_Urls::SPAM_DOC
			)
		);
	}

}
