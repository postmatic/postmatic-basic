<?php

class Prompt_Admin_Import_Options_Tab extends Prompt_Admin_Options_Tab {

	protected $import_type_name = 'import_type';
	protected $current_import_type = '';
	protected $jetpack_import_type = 'jetpack_import';

	public function name() {
		return __( 'Import Users', 'Postmatic' );
	}

	public function slug() {
		return 'import';
	}

	public function form_handler() {
		if ( isset( $_POST[$this->import_type_name] ) )
			$this->current_import_type = $_POST[$this->import_type_name];

		if ( $this->current_import_type )
			$this->add_notice( __('Import results are below.', 'Postmatic' ) );
	}

	protected function send_login_warning_content() {
		if ( !Prompt_Core::$options->get( 'send_login_info' ) )
			return '';

		return html( 'p class="send-login-warning"',
			html( 'strong', __( 'Important:', 'Postmatic' ) ),
			' ',
			__(
				'You have User Account notifications enabled in the Options tab, which means that each new subscriber imported will receive an email with their credentials. It is not necessary to send these credentials to Postmatic subscribers as all subscriber functions can be done directly via email. If you would like to disable these notifications please do so in the Options tab above.',
				'Postmatic'
			)
		);
	}

}
