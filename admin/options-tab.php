<?php

/**
 * Base class for option tabs on the settings page.
 *
 * Makes use of scbAdminPage techniques for outputting a form and saving options,
 * but instead of adding an admin page the UI is embedded in the Prompt settings page
 * by calling Prompt_Core::settings_page()->add_tab( $tab );
 */
class Prompt_Admin_Options_Tab extends scbAdminPage {

	/** @var array */
	protected $overridden_options;
	/** @var array */
	protected $notices;
	/** @var Prompt_Options */
	protected $options;

	/**
	 * Construct so the form is embeddable in another page rather than adding a new one.
	 * @param Prompt_Options $options
	 * @param null $overridden_options
	 */
	public function __construct( $options, $overridden_options = null ) {
		$this->options = $options;
		$this->overridden_options = $overridden_options;
		$this->nonce = '';
		$this->notices = array();
	}

	/**
	 * A name for the tab.
	 *
	 * Override the default for a nice name.
	 *
	 * @return string Tab name.
	 */
	public function name() {
		return str_replace( 'Prompt_', '', get_class( $this ) );
	}

	/**
	 * A CSS-style identifier for the tab.
	 *
	 * @return string Tab identifier.
	 */
	public function slug() {
		return sanitize_title( $this->name() );
	}

	/**
	 * Whether the tab is currently enabled.
	 * @return bool
	 */
	public function enabled() {
		return true;
	}

	/**
	 * A PHP form style identifier for the tab.
	 *
	 * @return string Tab action.
	 */
	public function action() {
		return str_replace( '-', '_', $this->slug() );
	}

	/**
	 * Adapt the page_content method to form_content
	 * @return string
	 */
	public function page_content() {
		return $this->form_content();
	}

	/**
	 * Generate the form markup.
	 * @return string form HTML
	 */
	public function render() {
		return 'This tab has no settings.';
	}

	/**
	 * Add button args to form tables.
	 * @param string $content
	 * @param array $button_args Optional button args.
	 * @return string
	 */
	public function form_table_wrap( $content, $button_args = array() ) {
		$content = $this->table_wrap( $content );
		return $this->form_wrap( $content, $button_args );
	}

	/**
	 * Generate a submit action based on the options key.
	 * @param string $content
	 * @param array $button_args
	 * @return string
	 */
	public function form_wrap( $content, $button_args = array() ) {
		$content .= html(
			'input',
			array( 'name' => 'tab', 'type' => 'hidden', 'value' => $this->slug() )
		);
		$content .= html(
			'input',
			array( 'name' => 'action', 'type' => 'hidden', 'value' => 'save_prompt_tab_options' )
		);
		$button_args = array_merge(
			array( 'action' => $this->action() . '_submit' ),
			$button_args
		);
		return parent::form_wrap( $content, $button_args );
	}

	/**
	 * Add an admin notice to display at the top of the parent page.
	 * @param $content
	 * @param string $class
	 */
	public function add_notice( $content, $class = 'updated' ) {
		$this->notices[] = array( $content, $class );

		if ( !has_action( 'admin_notices', array( $this, 'notices' ) ) )
			add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Display queued admin notices.
	 */
	public function notices() {
		foreach ( $this->notices as $notice ) {
			echo scb_admin_notice( $notice[0], $notice[1] );
		}
	}

	/**
	 * Set missing field names to false.
	 * @since 2.0.0
	 * @param array $new_data
	 * @param array $old_data
	 * @param array $field_names
	 * @return array
	 */
	protected function validate_checkbox_fields( $new_data, $old_data, $field_names ) {
		$valid_data = $old_data;

		if ( $this->overridden_options )
			$field_names = array_diff( $field_names, array_keys( $this->overridden_options ) );

		foreach ( $field_names as $field ) {
			if ( isset( $new_data[$field] ) )
				$valid_data[$field] = true;
			else
				$valid_data[$field] = false;
		}

		return $valid_data;
	}

	/**
	 * @since 2.1.0
	 * @return string
	 */
	protected function upgrade_url() {
		return admin_url( 'options-general.php?page=postmatic-pricing' );
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function upgrade_link() {
		return sprintf(
			__( '<a href="%s" class="%s">Upgrade</a>', 'Postmatic' ),
			$this->upgrade_url(),
			'upgrade_link'
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function download_labs_link() {
		return sprintf(
			'<a href="%s" class="download-modal labs">%s</a>',
			Prompt_Enum_Urls::DOWNLOAD_PREMIUM,
			__( 'Labs', 'Postmatic' )
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function download_premium_link() {
		return sprintf(
			'<a href="%s" class="install_link download-modal premium">%s</a>',
			Prompt_Enum_Urls::DOWNLOAD_PREMIUM,
			__( 'Install', 'Postmatic' )
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function contextual_download_link() {

		if ( $this->is_premium_active() ) {
			return '';
		}

		if ( $this->options->is_api_transport() ) {
			return $this->download_premium_link();
		}

		return $this->download_labs_link();
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	protected function is_premium_active() {
		return class_exists( 'Postmatic\Premium\Core' );
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	protected function is_digest_message_type_enabled() {
		return $this->is_message_type_enabled( Prompt_Enum_Message_Types::DIGEST );
	}

	/**
	 * @since 2.1.0
	 * @return bool
	 */
	protected function is_comment_digest_message_type_enabled() {
		return $this->is_message_type_enabled( Prompt_Enum_Message_Types::COMMENT_DIGEST );
	}

	/**
	 * @since 2.1.0
	 * @return bool
	 */
	protected function is_comment_moderation_message_type_enabled() {
		return $this->is_message_type_enabled( Prompt_Enum_Message_Types::COMMENT_MODERATION );
	}

	/**
	 * @since 2.1.0
	 * @param string $type The message type to check.
	 * @return bool
	 */
	protected function is_message_type_enabled( $type ) {
		return in_array( $type, $this->options->get( 'enabled_message_types' ) );
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function labs_tag() {
		return html( 'span class="labs"', __( 'Labs Feature', 'Postmatic' ) );
	}
}
