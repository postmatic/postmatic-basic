<?php

class Prompt_User_Mailing {

	/**
	 * Send an email to a user who has had an account created for them.
	 *
	 * @param int|object $user
	 * @param string $password
	 * @param Prompt_Template $template
	 */
	public static function send_new_user_notification( $user, $password, $template ) {
		$user = is_integer( $user ) ? get_userdata( $user ) : $user;

		$template_data = compact( 'user', 'password' );
		/**
		 * Filter new user email template data.
		 *
		 * @param array $template_data {
		 * @type WP_User $user
		 * @type string $password
		 * }
		 */
		$template_data = apply_filters( 'prompt/new_user_email/template_data', $template_data );

		$subject = sprintf( __( 'Welcome to %s', 'Postmatic' ), get_option( 'blogname' ) );

		$batch = Prompt_Email_Batch::make_for_single_recipient( array(
			'to_address' => $user->user_email,
			'from_address' => get_option( 'admin_email' ),
			'subject' => $subject,
			'html_content' => $template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		) );

		/**
		 * Filter new user email.
		 *
		 * @param Prompt_Email_Batch $batch
		 * @param array $template_data {
		 * @type WP_User $user
		 * @type string $password
		 * }
		 */
		$batch = apply_filters( 'prompt/new_user_batch', $batch, $template_data );

		Prompt_Factory::make_mailer( $batch )->send();
	}
}