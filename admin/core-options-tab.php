<?php

/**
 * Options tab for choosing modules
 *
 * @since 2.0.0
 *
 */
class Prompt_Admin_Core_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Options $options
	 * @param array|null $overridden_options
	 */
	public function __construct( $options, $overridden_options = null ) {
		parent::__construct( $options, $overridden_options );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Choose Modules', 'Postmatic' );
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

		$this->disable_unavailable_features();
	
		$parts = array(
			$this->promo_html(),
			html(
				'div class="intro-text"',
				html( 'h2', __( 'Get Started with Postmatic', 'Postmatic' ) ),
				html(
					'p',
					__( 'Build relationships, engage your community, and grow your platform using Postmatic.', 'Postmatic' )
				),
				$this->video_link( 'yjbVIBiSyYE' )
			),
		);

		$parts[] = $this->feature_chooser_html();

		$table_entries = array(
			array(
				'title' => __( 'Postmatic API Key', 'Postmatic' ),
				'type' => 'text',
				'name' => 'prompt_key',
				'extra' => array( 'class' => 'regular-text last-submit' ),
			),
		);

		$this->override_entries( $table_entries );

		$parts[] = $this->table( $table_entries, $this->options->get() );

		$parts[] = html( 'div id="manage-account"',
			html( 'a',
				array( 'href' => 'https://app.gopostmatic.com', 'target' => '_blank' ),
				__( '&#9998; Manage your account', 'Postmatic' )
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
			if ( isset( $this->overridden_options[$entry['name']] ) ) {
				$table_entries[$index]['extra'] = array(
					'class' => 'overridden',
					'disabled' => 'disabled',
				);
			}
		}
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function plan() {

		if ( Prompt_Enum_Email_Transports::LOCAL == $this->options->get( 'email_transport' ) )
			return 'free';

		if ( in_array( Prompt_Enum_Message_Types::DIGEST, $this->options->get( 'enabled_message_types' ) ) )
			return 'premium';

		return 'beta';
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param array $new_data
	 * @param array $old_data
	 * @return array
	 */
	function validate( $new_data, $old_data ) {

		$checkbox_fields = array(
			'enable_invites',
			'enable_optins',
			'enable_mailchimp_import',
			'enable_jetpack_import',
			'enable_mailpoet_import',
			'enable_post_delivery',
			'enable_digests',
			'enable_comment_delivery',
			'enable_webhooks',
			'enable_skimlinks',
			'enable_notes',
			'enable_analytics',
		);

		$valid_data = $this->validate_checkbox_fields( $new_data, $old_data, $checkbox_fields );

		if ( isset( $new_data['prompt_key'] ) and $new_data['prompt_key'] != $old_data['prompt_key'] ) {
			$valid_data = array_merge( $valid_data, $this->get_new_key_settings( $new_data['prompt_key'] ) );
		}

		if ( $old_data['enable_digests'] and !$valid_data['enable_digests'] ) {
			$valid_data['digest_plans'] = $this->options->get( 'digest_plans' );
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

		$new_settings = $this->options->get();
		$new_settings['prompt_key'] = $key;

		return $new_settings;
	}


	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function promo_html() {
		if ( $this->options->is_api_transport() ) {
			return '';
		}
		$template = new Prompt_Template( 'core-options-promo.php' );
		return $template->render();
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function feature_chooser_html() {

		$choosers = array(
			$this->audience_chooser_html(),
			$this->content_chooser_html(),
			$this->comment_chooser_html(),
			$this->monetize_chooser_html(),
		);

		return implode( '', $choosers );
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
				'href' => "https://www.youtube.com/embed/$video_id?autoplay=1&TB_iframe=true",
			),
			html(
				'span',
				__( 'Watch the Video', 'Postmatic' )
			)
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function audience_chooser_html() {

		$invite_label_attributes = array();
		$invite_upgrade_link = $this->labs_tag();
		$invite_attributes = array(
			'type' => 'checkbox',
			'name' => 'enable_invites',
			'value' => 1,
		);

		if ( !$this->is_premium_active() ) {
			$invite_label_attributes['class'] = 'disabled download-modal';
			$invite_upgrade_link = $this->contextual_download_link();
			$invite_attributes['extra'] = array( 'disabled' => 'disabled' );
		}

		return html(
			'fieldset class="chooser"',
			html( 'legend', __( 'Grow Your Audience', 'Postmatic' ) ),
			$this->video_link( 'MsPOztVrgZ4' ),
			html( 'label',
				$invite_label_attributes,
				$this->input( $invite_attributes, $this->options->get() ),
				html(
					'strong',
					__( 'Send Invitations', 'Postmatic' ),
					' ',
					$invite_upgrade_link,
					html( 'small', __( 'Turn past commenters into subscribers.', 'Postmatic' ) )
				)
			),
			$this->video_link( 'naDqSh-gvmo' ),
			$this->input(
				array(
					'type' => 'checkbox',
					'name' => 'enable_optins',
					'value' => 1,
					'desc' => html(
						'strong',
						__( 'Create Popups and Optins', 'Postmatic' ) .
						html(
							'small',
							__(
								'Generate leads and grown your list using popups, flyovers, topbars & more.',
								'Postmatic'
							)
						)
					),
				),
				$this->options->get()
			),
			$this->input(
				array(
					'type' => 'checkbox',
					'name' => 'enable_mailchimp_import',
					'value' => 1,
					'desc' => html(
						'strong',
						__( 'Import from Mailchimp', 'Postmatic' ) .
						html( 'small', __( 'Move your Mailchimp lists over to Postmatic.', 'Postmatic' ) )
					),
				),
				$this->options->get()
			),
			$this->input(
				array(
					'type' => 'checkbox',
					'name' => 'enable_jetpack_import',
					'value' => 1,
					'desc' => html(
						'strong',
						__( 'Import from Jetpack', 'Postmatic' ) .
						html( 'small', __( 'Import your Jetpack subscriber list.', 'Postmatic' ) )
					),
				),
				$this->options->get()
			),
			$this->input(
				array(
					'type' => 'checkbox',
					'name' => 'enable_mailpoet_import',
					'value' => 1,
					'desc' => html(
						'strong',
						__( 'Import from Mailpoet', 'Postmatic' ) .
						html( 'small', __( 'Import your MailPoet subscriber lists.', 'Postmatic' ) )
					),
				),
				$this->options->get()
			)
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function content_chooser_html() {
		$inputs = array(
			$this->video_link( 'wys_ycyNIUg' ),
			$this->input(
				array(
					'type' => 'checkbox',
					'name' => 'enable_post_delivery',
					'value' => 1,
					'desc' => html(
						'strong',
						__( 'Send Posts', 'Postmatic' ) .
						html( 'small', __( 'Deliver posts as replyable emails.', 'Postmatic' ) )
					),
				),
				$this->options->get()
			)
		);

		$digest_label_attributes = array();
		$digest_upgrade_link = '';
		$digest_attributes = array(
			'type' => 'checkbox',
			'name' => 'enable_digests',
			'value' => 1,
		);

		if ( !in_array( Prompt_Enum_Message_Types::DIGEST, $this->options->get( 'enabled_message_types' ) ) ) {

			$digest_label_attributes['class'] = 'disabled';
			$digest_upgrade_link = $this->upgrade_link();
			$digest_attributes['extra'] = array( 'disabled' => 'disabled' );

		} else if ( !$this->is_premium_active() ) {

			$digest_label_attributes['class'] = 'disabled download-modal';
			$digest_upgrade_link = $this->download_premium_link();
			$digest_attributes['extra'] = array( 'disabled' => 'disabled' );

		}
		
		$inputs[] = $this->video_link( 'J-k5IZcKsNk' );
		
		$inputs[] = html(
			'label',
			$digest_label_attributes,
			$this->input( $digest_attributes, $this->options->get() ),
			html(
				'strong',
				__( 'Send Digests', 'Postmatic' ),
				' ',
				$digest_upgrade_link,
				html( 'small', __( 'Send automatic daily, weekly, or monthly digests of posts.', 'Postmatic' ) )
			)
		);

		$notes_label_attributes = array();
		$notes_attributes = array(
			'type' => 'checkbox',
			'name' => 'enable_notes',
			'value' => 1,
		);

		$inputs[] = html(
			'label',
			$notes_label_attributes,
			$this->input( $notes_attributes, $this->options->get() ),
			html(
				'strong',
				__( 'Preview Postmatic Notes', 'Postmatic' ) .
				html(
					'small',
					__( 'Send replyable letters and private correspondence to your community.', 'Postmatic' )
				)
			),
			__( 'Coming soon to Postmatic Premium but you can check it out now.', 'Postmatic' )
		);

		return html(
			'fieldset class="chooser"',
			html( 'legend', __( 'Deliver Your Content', 'Postmatic' ) ),
			implode( '', $inputs )
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function comment_chooser_html() {
		$asides = array();

		if ( !defined( 'EPOCH_VER' ) ) {
			$asides[] = html(
				'aside',
				html( 'h3', __( 'Make commenting fun with Epoch', 'Postmatic' ) ),
				html(
					'p',
					__(
						'<a href="http://gopostmatic.com/epoch" target="_blank">Epoch</a> is a free, private, and native alternative to Disqus. Your users will love it and your site speed score will as well.',
						'Postmatic'
					)
				),
				html(
					'a class="button"',
					array( 'href' => wp_nonce_url(
						admin_url( 'update.php?action=install-plugin&plugin=epoch' ),
						'install-plugin_epoch'
					) ),
					__( 'Install Epoch', 'Postmatic' )
				)
			);
		}

		if ( !class_exists( 'Postmatic_Social' ) ) {
			$asides[] = html(
				'aside',
				html( 'h3', __( 'Enable Social Commenting', 'Postmatic' ) ),
				html(
					'p',
					__(
						'Install Postmatic Social Commenting, a tiny, fast, and convenient way to let your readers comment using their social profiles.',
						'Postmatic'
					)
				),
				html(
					'a class="button"',
					array( 'href' => wp_nonce_url(
						admin_url( 'update.php?action=install-plugin&plugin=postmatic-social-commenting' ),
						'install-plugin_postmatic-social-commenting'
					) ),
					__( 'Install Social Commenting', 'Postmatic' )
				)
			);
		}

		return html(
			'fieldset class="chooser"',
			html( 'legend', __( 'Engage Your Readers', 'Postmatic' ) ),
			$this->video_link( '8y2pzTmliu4' ),
			$this->input(
				array(
					'type' => 'checkbox',
					'name' => 'enable_comment_delivery',
					'value' => 1,
					'desc' => html(
						'strong',
						__( 'Comments by Email', 'Postmatic' ) .
						html(
							'small',
							__( 'Let users subscribe to comments - and reply from their inbox.', 'Postmatic' )
						)
					),
				),
				$this->options->get()
			),
			implode( '', $asides )
		);
	}

	/**
	 * @since 2.0.0
	 */
	protected function disable_unavailable_features() {
		if ( $this->is_premium_active() ) {
			return;
		}

		$options = array( 'enable_invites', 'enable_webhooks', 'enable_digests', 'enable_skimlinks' );

		foreach ( $options as $option ) {
			if ( $this->options->get( $option ) ) {
				$this->options->set( $option, false );
			}
		}
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function monetize_chooser_html() {

		$upgrade_link = '';

		$webhooks_label_attributes = array();
		$webhooks_upgrade_link = $this->labs_tag();
		$webhooks_attributes = array(
			'type' => 'checkbox',
			'name' => 'enable_webhooks',
			'value' => 1,
		);

		$skimlinks_label_attributes = array();
		$skimlinks_attributes = array(
			'type' => 'checkbox',
			'name' => 'enable_skimlinks',
			'value' => 1,
		);

		$analytics_label_attributes = array();
		$analytics_attributes = array(
			'type' => 'checkbox',
			'name' => 'enable_analytics',
			'value' => 1,
		);

		if ( !$this->is_premium_active() ) {

			$upgrade_link = $this->contextual_download_link();

			$webhooks_label_attributes['class'] = 'disabled download-modal';
			$webhooks_upgrade_link = $this->contextual_download_link();
			$webhooks_attributes['extra'] = array( 'disabled' => 'disabled' );

			$skimlinks_label_attributes['class'] = 'disabled download-modal';
			$skimlinks_attributes['extra'] = array( 'disabled' => 'disabled' );

		}

		if ( !$this->options->is_api_transport() ) {

			$upgrade_link = $this->upgrade_link();

			$skimlinks_label_attributes['class'] = 'disabled';
			$skimlinks_attributes['extra'] = array( 'disabled' => 'disabled' );

		}

		return html(
			'fieldset class="chooser"',
			html( 'legend', __( 'Analyze & Monetize', 'Postmatic' ) ),
			html(
				'label',
				$webhooks_label_attributes,
				$this->input( $webhooks_attributes, $this->options->get() ),
				html(
					'strong',
					__( 'Enable Webhooks', 'Postmatic' ),
					' ',
					$webhooks_upgrade_link,
					html( 'small', __( 'Use webhooks with services like Zapier to keep your lists in sync.', 'Postmatic' ) )
				)
			),
			html(
				'label',
				$skimlinks_label_attributes,
				$this->input( $skimlinks_attributes, $this->options->get() ),
				html(
					'strong',
					__( 'Enable Skimlinks', 'Postmatic' ),
					' ',
					$upgrade_link,
					html( 'small', __( 'Use Skimlinks in all your emailed content.', 'Postmatic' ) )
				)
			),
			html(
				'label',
				$analytics_label_attributes,
				$this->input( $analytics_attributes, $this->options->get() ),
				html(
					'strong',
					__( 'Preview Postmatic Analytics', 'Postmatic' ),
					' ',
					html( 'small', __( 'A whole lot more than open rates.', 'Postmatic' ) )
				),
				__( 'Help us Kickstart community intelligence', 'Postmatic' )
			)
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function upgrade_link() {
		return sprintf(
			__( '<a href="%s" class="upgrade_link">Premium</a>', 'Postmatic' ),
			Prompt_Enum_Urls::MANAGE
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
	 * @return string
	 */
	protected function labs_tag() {
		return html( 'span class="labs"', __( 'Labs Feature', 'Postmatic' ) );
	}
}