<?php

class Prompt_Admin_Jetpack_Import_Options_Tab extends Prompt_Admin_Import_Options_Tab {

	public function name() {
		return __( 'Jetpack Import', 'Postmatic' );
	}

	public function slug() {
		return 'jetpack-import';
	}

	public function render() {
		$content = html( 'h2', __( 'Jetpack Import', 'Postmatic' ) );

		if ( ! Prompt_Admin_Jetpack_Import::is_usable() )
			return $content . $this->jetpack_not_usable_content( Prompt_Admin_Jetpack_Import::not_usable_message() );

		if ( $this->jetpack_import_type == $this->current_import_type )
			return $content . $this->jetpack_import_content();

		return $content . $this->jetpack_ready_content();
	}

	protected function jetpack_not_usable_content( $message ) {
		return html( 'div id="jetpack-not-ready"', html( 'p', $message ) );
	}

	protected function jetpack_ready_content() {
		$content = html( 'div id="jetpack-import-instructions"',
			html( 'p',
				__(
					'Migrating your users from Jetpack to Postmatic takes only seconds. We’ve built a safe and secure importer which will copy over all of your new post notification Jetpack subscribers with a single click. Once the import has completed you can safely disable Jetpack Subscriptions and continue on doing what you do best. Your Jetpack subscribers will be left in tact should you ever need to access them again.',
					'Postmatic'
				)
			),
			html( 'div',
				html( 'h3', __( 'Jetpack Import FAQ', 'Postmatic' ) ),
				html( 'h4', __( 'Will my subscribers be alerted to the change?', 'Postmatic' ) ),
				html( 'p',
					__(
						'No. The import process is invisible to subscribers. They will not be alerted nor will they need to opt-in again.',
						'Postmatic'
					)
				),
				html( 'h4', __( 'What kind of subscribers are imported?', 'Postmatic' ) ),
				html( 'p',
					__(
						'People who have subscribed to new posts on your site will be imported. At this time it\'s not going to be in the cards to import people that have subscribed only to comments on individual posts.',
						'Postmatic'
					)
				),
				html( 'h4', __( 'Who isn\'t imported?', 'Postmatic' ) ),
				html( 'p',
					__(
						'Jetpack supports two kinds of users: people that subscribe to your site with their email address, and people that subscribe to your site with their wordpress.com user identity. At this time we can\'t access the email address of a user which subscribed with their wordpress.com identity. This will in most cases be a very small percentage of your audience.',
						'Postmatic'
					)
				),
				html( 'h4',
					__( 'What happens if something goes wrong? Will my Jetpack subscribers be safe?', 'Postmatic' )
				),
				html( 'p',
					__(
						'If something goes wrong with the import you will be notified of the error. Your Jetpack subscribers list lives on wordpress.com and will always be there in case you need it again.',
						'Postmatic'
					)
				)
			)
		);

		$content .= html( 'div id="jetpack-import-intro"',
			__( 'Everything on your server looks good. We are ready to import your Jetpack subscribers.', 'Postmatic' )
		);

		$content .= $this->send_login_warning_content();

		$content .= html( 'input',
			array( 'name' => 'import_type', 'type' => 'hidden', 'value' => $this->jetpack_import_type )
		);

		return $this->form_wrap( $content, array( 'value' => __( 'Import from Jetpack' ) ) );
	}

	protected function jetpack_import_content() {
		$jetpack_import = Prompt_Factory::make_jetpack_import();

		$jetpack_import->execute();

		$content = $jetpack_import->get_error() ? $jetpack_import->get_error()->get_error_message() : '';

		$results_format = _n(
			'We have imported one subscriber. It is now safe to disable Jetpack commenting and subscriptions.',
			'Imported %1$s subscribers. It is now safe to disable Jetpack commenting and subscriptions.',
			$jetpack_import->get_imported_count(),
			'Postmatic'
		);

		if ( $jetpack_import->get_already_subscribed_count() > 0 ) {
			$results_format .= ' ' . _n(
				'The one user we found was already subscribed.',
				'The %2$s users we found were already subscribed.',
				$jetpack_import->get_already_subscribed_count(),
				'Postmatic'
			);
		}

		$content .= ' ' . sprintf(
			$results_format,
			$jetpack_import->get_imported_count(),
			$jetpack_import->get_already_subscribed_count()
		);

		return $content;
	}
}