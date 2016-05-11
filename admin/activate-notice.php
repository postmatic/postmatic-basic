<?php

class Prompt_Admin_Activate_Notice {

	/** @var  string Current API key */
	protected $key;
	/** @var  Prompt_Admin_Options_Page  */
	protected $options_page;

	public function __construct( $key, Prompt_Admin_Options_Page $options_page ) {
		$this->key = $key;
		$this->options_page = $options_page;
		add_action( 'admin_notices', array( $this, 'maybe_display' ) );
	}

	/**
	 * Display an admin notice when a key is needed, and we are not on the settings page.
	 */
	public function maybe_display() {

		if ( $this->key or ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $this->options_page->is_current_page() ) {
			return;
		}

		$template = new Prompt_Template( 'activate-account-notice.php' );

		echo $template->render( array( 'options_page_url' => $this->options_page->url() ) );
	}
}