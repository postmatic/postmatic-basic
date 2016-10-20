<?php

class Prompt_Admin_Download_Modal_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var string override the option key */
	protected $skip_option_key = 'skip_download_intro';

	/**
	 * Display (echo) the notice if conditions are met.
	 *
	 * Does nothing if conditions fail or the notice has been dismissed.
	 *
	 * @since 1.2.3
	 */
	public function maybe_display() {
		if ( ! class_exists( 'Postmatic\Premium\Core' ) ) {
			echo $this->render();
			Prompt_Core::labs_notice()->dismiss();
		}
	}

	/**
	 * Render download modal markup if the premium plugin is not installed.
	 *
	 * The modal javascript is currently options-page.js:init_download_modal().
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render() {

		if ( class_exists( '\Postmatic\Premium\Core' ) ) {
			return '';
		}

		$data = array(
			'is_api_transport' => ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) ),
			'upload_url' => admin_url( 'plugin-install.php?tab=upload' ),
		);

		$template = new Prompt_Template( 'download-modal.php' );

		return $template->render( $data );
	}

}
