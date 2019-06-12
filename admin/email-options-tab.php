<?php

/**
 * Email template options tab
 * @since 1.0.0
 */
class Prompt_Admin_Email_Options_Tab extends Prompt_Admin_Options_Tab {

	/** @var Prompt_Stylify */
	protected $stylify;

	/**
	 * @since 1.0.0
	 * @param bool|string $options
	 * @param null        $overridden_options
	 */
	public function __construct( $options, $overridden_options = null ) {
		parent::__construct( $options, $overridden_options );
		$this->stylify = new Prompt_Stylify( Prompt_Core::$options->get( 'site_styles' ) );
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function name() {
		return __( 'Configure Your Template', 'Postmatic' );
	}

	/**
	 * @since 1.0.0
	 */
	public function form_handler() {

		if ( !empty( $_POST['stylify_button'] ) ) {
			$status = $this->stylify->refresh();
			$message = is_wp_error( $status ) ? $status->get_error_message() : __( 'Colors updated.', 'Postmatic' );
			$class = is_wp_error( $status ) ? 'error' : 'updated';
			Prompt_Core::$options->set( 'site_styles', $this->stylify->get_styles() );
			$this->add_notice( $message, $class );
			return;
		}

		if ( !empty( $_POST['reset_site_styles_button'] ) ) {
			Prompt_Core::$options->set( 'site_styles', array() );
			$this->stylify = new Prompt_Stylify( array() );
			$this->add_notice( __( 'Colors set to defaults.', 'Postmatic' ) );
			return;
		}

		if ( !empty( $_POST['send_test_email_button'] ) ) {

			$to_address = sanitize_email( $_POST['test_email_address'] );

			if ( !is_email( $to_address ) ) {
				$this->add_notice(
					__( 'Test email was <strong>not sent</strong> to an invalid address.', 'Postmatic' ),
					'error'
				);
				return;
			}

			$html_template = new Prompt_Template( 'test-email.php' );

			$footnote = __(
				'This is a test email sent by Replyable. It is solely for testing email delivery and is not replyable.',
				'Postmatic'
			);

			$subject = __( 'This is a test email. By Replyable.', 'Postmatic' ) . ' ' .
				date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

			$batch = new Prompt_Email_Batch( array(
				'subject' => $subject,
				'html_content' => $html_template->render(),
				'message_type' => Prompt_Enum_Message_Types::ADMIN,
				'footnote_html' => $footnote,
				'footnote_text' => $footnote,
			) );
			$batch->add_individual_message_values( array( 'to_address' => $to_address ) );

			if ( !is_wp_error( Prompt_Factory::make_mailer( $batch )->send() ) ) {
				$this->add_notice( __( 'Test email <strong>sent</strong>.', 'Postmatic' ) );
				return;
			}

		}

		parent::form_handler();
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function render() {

		$introduction = html(
			'div class="intro-text"',
			html( 'h2', __( 'Customize your Replyable template', 'Postmatic' ) ),
			html( 'p',
				__( 'Since we\'ll be sending via email the focus should be on <em>the comments</em>. That\'s why we keep things simple. Configure your colors, header and footer. Replyable will handle what goes in between.',
					'Postmatic'
				)
			)
		);

		ob_start();
		wp_editor(
			$this->options->get( 'subscribed_introduction' ),
			'subscribed_introduction',
			array(
				'editor_height' => 400,
			)
		);
		$subscriber_welcome_editor = ob_get_clean();

		$subscriber_welcome_content = html( 'div id="subscriber-welcome-message"',
			html( 'h3', __( 'Custom welcome message', 'Postmatic' ) ),
			html( 'p', __( 'When someone sucessfully subscribes to a conversation we\'ll shoot back a confirmation note. Use this as a place to say thanks, or welcome them to come back and read some other posts.', 'Postmatic' ) ),
			$subscriber_welcome_editor
		);

		$content = $this->table_wrap( implode( '', $this->get_rows() ) ) . $subscriber_welcome_content;

		return
			$introduction .
			$this->form_wrap( $content ) . $this->footer();
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

		if ( $this->options->is_api_transport() ) {
			$valid_data = $this->validate_checkbox_fields( $new_data, $old_data, array( 'email_footer_credit' ) );
		}

		if ( isset( $new_data['email_header_text'] ) ) {
			$valid_data['email_header_text'] = sanitize_text_field( $new_data['email_header_text'] );
		}

		if ( isset( $new_data['email_footer_text'] ) ) {
			$valid_data['email_footer_text'] = sanitize_text_field( $new_data['email_footer_text'] );
		}

		if ( isset( $new_data['subscribed_introduction'] ) ) {
			$valid_data['subscribed_introduction'] = stripslashes(
				wp_kses_post( $new_data['subscribed_introduction'] )
			);
		}

		return $valid_data;
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function footer() {

		if ( Prompt_Enum_Email_Transports::LOCAL != $this->options->get( 'email_transport' ) )
			return '';

		$footer_template = new Prompt_Template( 'email-options-tab-footer.php' );

		$data = array(
			'upgrade_url' => Prompt_Enum_Urls::PREMIUM,
			'image_url' => path_join( Prompt_Core::$url_path, 'media/screenshots.jpg' ),
		);

		return $footer_template->render( $data );
	}

	/**
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_rows() {

		$rows = array();

		$style_reset_html = '';
		if ( $this->stylify->get_styles() ) {
			$style_reset_html = html(
				'input class="button" type="submit" name="reset_site_styles_button"',
				array( 'value' => __( 'Use defaults', 'Postmatic' ) )
			);
		}

		$rows[] = html(
			'tr class="stylify"',
			html( 'th scope="row"',
				__( 'Color palette detection', 'Postmatic' ),
				'<br/>',
				html( 'small',
					__(
						'Want the Replyable template to use your typography and colors? Do so with a single click. We\'ll analyze the active theme and make your email template follow suit.',
						'Postmatic'
					)
				)
			),
			html(
				'td',
				html( 'span class="site-color"',
					array( 'style' => 'background-color: ' . $this->stylify->get_value( 'a', 'color', '#000' ) )
				),
				html( 'span class="site-color"',
					array( 'style' => 'background-color: ' . $this->stylify->get_value( 'h1', 'color', '#000' ) )
				),
				html( 'span class="site-color"',
					array( 'style' => 'background-color: ' . $this->stylify->get_value( 'h2', 'color', '#000' ) )
				),
				html( 'span class="site-color"',
					array( 'style' => 'background-color: ' . $this->stylify->get_value( 'h3', 'color', '#000' ) )
				),
				html( 'span class="site-color"',
					array( 'style' => 'background-color: ' . $this->stylify->get_value( 'h4', 'color', '#000' ) )
				),
				html( 'div',
					html(
						'input class="button" type="submit" name="stylify_button"',
						array( 'value' => __( 'Refresh', 'Postmatic' ) )
					),
					$style_reset_html
				)
			)
		);

		$rows[] = html(
			'tr class="email-header-text"',
			html( 'th scope="row"', __( 'Email header text', 'Postmatic' ),
			'<br/>',
					html( 'small',
						__(
							'This text will show next to your site icon in simpler transactional emails such as comment notifications.',
							'Postmatic'
						)
				)
			),
			html(
				'td',
				$this->input(
					array( 'name' => 'email_header_text', 'type' => 'text', 'extra' => 'class=last-submit' ),
					$this->options->get()
				)
			)
		);

		$rows[] = html(
			'tr class="email-footer-text"',
			html( 'th scope="row"', __( 'Email footer text', 'Postmatic' ) ),
			html(
				'td',
				$this->input(
					array( 'name' => 'email_footer_text', 'type' => 'text', 'extra' => 'class=last-submit' ),
					$this->options->get()
				)
			)
		);

		$rows[] = html(
			'tr',
			html( 'th scope="row"', __( 'Send a test email to', 'Postmatic' ) ),
			html(
				'td',
				$this->input(
					array(
						'type' => 'text',
						'name' => 'test_email_address',
						'value' => wp_get_current_user()->user_email,
						'extra' => 'class=no-submit',
					),
					$_POST
				),
				html(
					'input class="button" type="submit" name="send_test_email_button"',
					array( 'value' => __( 'Send', 'Postmatic' ) )
				)
			)
		);

		if ( $this->options->is_api_transport() ) {
		    $rows[] = $this->footer_credit_row();
        }

		return $rows;
	}

    /**
     * @since 0.5.0
     * @return string
     */
    protected function footer_credit_row() {
        return html(
            'tr class="email-footer-credit"',
            html( 'th scope="row"', __( 'Share the love?', 'postmatic-premium' ) ),
            html(
                'td',
                $this->input(
                    array(
                        'name' => 'email_footer_credit',
                        'type' => 'checkbox',
                        'desc' => __( 'Include "Delivered by Replyable" in the footer area. We appreciate it!', 'postmatic-premium' ),
                        'extra' => 'class=last-submit',
                    ),
                    $this->options->get()
                )
            )
        );
    }
}
