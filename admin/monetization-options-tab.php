<?php

/**
 * A tab to promote Analytics
 * @since 2.0.6
 */
class Prompt_Admin_Monetization_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 * @since 2.0.6
	 * @return string
	 */
	public function name() {
		return __( 'Monetization', 'Postmatic' );
	}

	/**
	 * @since 2.0.6
	 * @return string
	 */
	public function slug() {
		return 'monetization';
	}

	/**
	 * @since 2.0.6
	 * @return string
	 */
	public function render() {
		$template = new Prompt_Template( 'monetization-tab.php' );
		return $this->form_wrap( $template->render(), $submit_button = false );
	}

	/**
	 * @since 2.0.6
	 */
	public function form_handler() {
		
		if ( empty( $_POST['request_contact_button'] ) ) {
			return;
		}

		$user = wp_get_current_user();
		
		$batch = Prompt_Email_Batch::make_for_single_recipient( array(
			'to_address' => Prompt_Core::SUPPORT_EMAIL,
			'from_address' => $user->exists() ? $user->user_email : get_option( 'admin_email' ),
			'from_name' => $user->exists() ? $user->display_name : '',
			'subject' => sprintf( 'Monetization contact request from %s', html_entity_decode( get_option( 'blogname' ) ) ),
			'text_content' => sprintf( 'Reply with more info for %s.', home_url() ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		) );

		if ( !is_wp_error( Prompt_Factory::make_mailer( $batch )->send() ) ) {
			$this->add_notice( __( 'Request sent - you will hear from us soon about monetization options tailored to your site.', 'Postmatic' ) );
		}
			
	}

}
