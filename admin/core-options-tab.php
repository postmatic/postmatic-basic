<?php

/**
 * Options tab for choosing modules
 *
 * @since 2.0.0
 *
 */
class Prompt_Admin_Core_Options_Tab extends Prompt_Admin_Options_Tab {
	/** @var  Prompt_Interface_License_Status */
	protected $license_status;

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Options $options
	 * @param array|null $overridden_options
	 * @param Prompt_Interface_License_Status $license_status
	 */
	public function __construct( $options, $overridden_options = null, Prompt_Interface_License_Status $license_status = null ) {
		$this->license_status = $license_status;
		parent::__construct( $options, $overridden_options );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Get Started', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'core';
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function render() {

		$parts = array(
			$this->promo_html(),
			html(
				'div class="intro-text welcome"',
				html( 'h2', __( 'Welcome to Replyable', 'Postmatic' ) ),
				html(
					'p',
					__( 'Configure Replyable using the tabs above, or get help below.', 'Postmatic' )
				)
			),
		);

		$buttons = array();

		if ( $this->license_status->is_paying() || $this->license_status->is_trial_underway() ) {
			$buttons[] = html(
				'li id="util-account"',
				html(
					'a class="btn-postmatic"',
					array( 'href' => admin_url( 'options-general.php?page=postmatic-account' ) ),
					__( 'Manage your account', 'Postmatic' )
				)
			);
		}

		$buttons[] = html(
			'li id="util-contact"',
			html(
				'a class="btn-postmatic"',
				array( 'href' => admin_url( 'options-general.php?page=postmatic-contact' ) ),
				__( 'Contact us', 'Postmatic' )
			)
		);

		$buttons[] = html(
			'li id="util-docs"',
			html(
				'a class="btn-postmatic" target="_blank"',
				array( 'href' => 'http://docs.replyable.com' ),
				__( 'Read the docs', 'Postmatic' )
			)
		);

		$parts[] = html( 'ul id="replyable-utils"', implode( '', $buttons ) );

		$parts[] = html(
			'div class="key"',
			html(
				'label',
				__( 'Your Replyable API Key (used for troubleshooting)', 'Postmatic' ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'prompt_key',
						'extra' => array( 'class' => 'regular-text last-submit' ),
					),
					$this->options->get()
				)
			)
		);

		return $this->form_wrap( implode( '', $parts ) );
	}

	/**
	 * Disable overridden entry UI table entries.
	 *
	 * @since 2.0.0
	 *
	 * @param array $table_entries
	 */
	protected function override_entries( &$table_entries ) {
		foreach ( $table_entries as $index => $entry ) {
			if ( isset( $this->overridden_options[ $entry['name'] ] ) ) {
				$table_entries[ $index ]['extra'] = array(
					'class'    => 'overridden',
					'disabled' => 'disabled',
				);
			}
		}
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param array $new_data
	 * @param array $old_data
	 * @return array
	 */
	public function validate( $new_data, $old_data ) {

		$valid_data = $old_data;

		if ( isset( $new_data['prompt_key'] ) && $new_data['prompt_key'] !== $old_data['prompt_key'] ) {
			$valid_data = array_merge( $valid_data, $this->get_new_key_settings( $new_data['prompt_key'] ) );
		}

		return $valid_data;
	}

	/**
	 * Validate a new key and return revised settings to go with it.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @return array
	 */
	protected function get_new_key_settings( $key ) {
		$key = Prompt_Core::settings_page()->validate_key( $key );

		if ( is_wp_error( $key ) ) {
			add_settings_error( 'prompt_key', 'invalid_key', $key->get_error_message() );
			return array();
		}

		$new_settings               = $this->options->get();
		$new_settings['prompt_key'] = $key;

		return $new_settings;
	}


	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function promo_html() {
		$data = array(
			'is_pending_activation' => $this->license_status->is_pending_activation(),
			'is_trial_available'    => $this->license_status->is_trial_available(),
			'is_trial_underway'     => $this->license_status->is_trial_underway(),
			'is_paying'             => $this->license_status->is_paying(),
			'is_key_present'        => (bool) $this->options->get( 'prompt_key' ),
			'is_api_transport'      => (bool) $this->options->is_api_transport(),
			'has_changed_licenses'  => (bool) $this->options->get( 'freemius_license_changes' ),
		);

		$template = new Prompt_Template( 'core-options-promo.php' );

		return $template->render( $data );
	}

	/**
	 * @since 2.0.6
	 * @param string $video_id
	 * @return string
	 */
	protected function video_link( $video_id ) {
		return html(
			'a',
			array(
				'class' => 'thickbox video',
				'href'  => "https://www.youtube.com/embed/$video_id?autoplay=1&TB_iframe=true",
			),
			html(
				'span',
				__( 'Watch the Video', 'Postmatic' )
			)
		);
	}
}
