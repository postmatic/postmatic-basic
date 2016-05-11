<?php

class Prompt_Admin_Moderation_User_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var string override the option key */
	protected $skip_option_key = 'skip_moderation_user_intro';

	/**
	 * Render a message if moderation is on and the admin email has no corresponding user.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function render() {

		if ( ! get_option( 'moderation_notify' ) or ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		$enabled_message_types = Prompt_Core::$options->get( 'enabled_message_types' );

		if ( !in_array( Prompt_Enum_Message_Types::COMMENT_MODERATION, $enabled_message_types ) ) {
			return '';
		}

		$admin_email = get_option( 'admin_email' );

		$moderator = get_user_by( 'email', $admin_email );

		if ( $moderator ) {
			return '';
		}

		return $this->render_message(
			sprintf(
				__(
					'The email address in Settings / General / E-mail Address does not match any user. Postmatic is great for moderating comments via email, but it must act on behalf of a user with permission to do this. You can either create an admin user with email address %s, or change the site E-mail address setting to the address of the user who will moderate comments.',
					'Postmatic'
				),
				$admin_email
			)
		);
	}

}
