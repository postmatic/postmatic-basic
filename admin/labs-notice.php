<?php

/**
 * Manage the notice to upgrade to labs.
 * @since 2.0.13
 */
class Prompt_Admin_Labs_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var  string */
	protected $skip_option_key = 'skip_labs_notice';
	/** @var  string Current API key */
	protected $key;
	/** @var  Prompt_Admin_Options_Page  */
	protected $options_page;
	/** @var  Prompt_Options  */
	protected $options;

	/**
	 * Prompt_Admin_Labs_Notice constructor.
	 *
	 * @since 2.0.13
	 * @param Prompt_Admin_Options_Page $options_page
	 * @param Prompt_Options $options
	 */
	public function __construct( Prompt_Admin_Options_Page $options_page, Prompt_Options $options ) {
		$this->options_page = $options_page;
		$this->options = $options;
		$this->key = $this->options->get( 'prompt_key' );
		add_action( 'admin_notices', array( $this, 'maybe_display' ) );
	}

	/**
	 * Display an admin notice when a key is present and we are not on the settings page.
	 */
	public function maybe_display() {

		if ( ! $this->key or ! $this->options->get( 'enable_post_delivery' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( class_exists( 'Postmatic\Premium\Core' ) ) {
			return;
		}

		if ( $this->options_page->is_current_page() ) {
			return;
		}

		if ( $this->is_dismissed() ) {
			return;
		}

		$this->options->set( 'skip_download_intro', false );

		echo $this->render();
	}

	/**
	 * @since 2.0.13
	 * @return string
	 */
	public function render() {
		$template = new Prompt_Template( 'labs-notice.php' );

		return $template->render( array(
			'options_page_url' => $this->options_page->url(),
			'dismiss_url' => $this->dismiss_url(),
		) );
	}
}