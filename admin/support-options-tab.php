<?php

class Prompt_Admin_Support_Options_Tab extends Prompt_Admin_Options_Tab {

	protected $send_diagnostics_name = 'send_diagnostic_report';

	public function name() {
		return __( 'Get Support', 'Postmatic' );
	}

	public function form_handler() {

		$environment = new Prompt_Environment();

		$user = wp_get_current_user();

		$email = Prompt_Email_Batch::make_for_single_recipient( array(
			'to_address' => Prompt_Core::SUPPORT_EMAIL,
			'from_address' => $user->user_email,
			'from_name' => $user->display_name,
			'subject' => sprintf(
				__( 'Diagnostics from %s', 'Postmatic' ), html_entity_decode( get_option( 'blogname' ) )
			),
			'html_content' => json_encode( $environment->to_array() ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		) );

		$sent = Prompt_Factory::make_mailer( $email )->send();

		if ( is_wp_error( $sent ) ) {
			Prompt_Logging::add_error(
				'diagnostic_submission_error',
				__( 'Diagnostics could not be sent, please try a bug report.', 'Postmatic' ),
				$sent
			);
			return;
		}

		$this->add_notice( __( 'Diagnostics <strong>sent</strong>.', 'Postmatic' ) );
	}

	public function render() {
		$content = html( 'div class="intro-text"',
			html( 'h2', __( 'Need Some Help?', 'Postmatic' ) )
			);

		$content .= html( 'div id="postmatic-documentation" class="widget"',
			html( 'h3', __( 'Documentation', 'Postmatic' ) ),
			html( 'p', __( 'Find answers to the most common questions and ask your own.', 'Postmatic' ) ),
			html( 'p',
				html( 'a',
					array( 'href' => Prompt_Enum_Urls::DOCS, 'target' => '_blank' ),
					__( 'Find Answers', 'Postmatic' )
				)
			)
		);

		$content .= html( 'div id="postmatic-widget-directory" class="widget"',
			html( 'h3', __( 'Widget Directory', 'Postmatic' ) ),
			html( 'p', __( 'We\'ve hand curated dozens of widgets. Get the most out of your email template.', 'Postmatic' ) ),
			html( 'p',
				html( 'a',
					array( 'href' => Prompt_Enum_Urls::WIDGET_DIRECTORY ),
					__( 'Research Widgets', 'Postmatic' )
				)
			)
		);

		$content .= html( 'div id="postmatic-support" class="widget"',
			html( 'h3', __( 'Get Support', 'Postmatic' ) ),
			html( 'p', __( 'Let us know if something isn\'t right. We\'ll fix it right away.', 'Postmatic' ) ),
			html( 'p',
				html( 'a',
					array( 'href' => Prompt_Enum_Urls::BUG_REPORTS ),
					__( 'Submit a Ticket', 'Postmatic' )
				)
			)
		);

		return $this->form_wrap( $content, array( 'value' => __( 'Advanced: Send Diagnostic Info to Support', 'Postmatic') ) );
	}

}
