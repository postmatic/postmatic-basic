<?php

/**
 * Subscribe to Comments Reloaded import interface
 * @since 1.2.3
 */
class Prompt_Admin_Subscribe_Reloaded_Import_Options_Tab extends Prompt_Admin_Import_Options_Tab {

	protected $import_list_name = 'import_list';
	protected $rejected_addresses_name = 'rejected_addresses';
	protected $import_type = 'subscribe-reloaded-import';

	public function name() {
		return __( 'Importer', 'Postmatic' );
	}

	public function slug() {
		return 'import-subscribe-reloaded';
	}

	public function is_available() {
		if ( Prompt_Core::$options->get( 'scr_import_done' ) ) {
			return false;
		}
		return $this->table_exists() or $this->is_importable_comments_plugin_active();
	}

	public function render() {
		$content = html(
			'div class="intro-text scr-import"',
			html( 'h2', __( 'Migrate Comment Subscriptions', 'Postmatic' ) ),
			html( 'p',
				__(
					'This utility will migrate comment subscriptions from Subscribe to Comments, Subscribe to Comments Reloaded, and Subscribe to Double-Opt-In Comments.',
					'Postmatic'
				)
			),
			html( 'p',
				__(
					'Subscribers are imported silently and without notice sent. When the import has finished you can disable your legacy subscription plugin.',
					'Postmatic' )
			),
			html( 'h3', __( 'Important information before you get started:', 'Postmatic' ) ),
			html( 'p',
				sprintf(
					__(
						'You must be running Subscribe to Comments <strong>Reloaded</strong> to use this migration tool. Please upgrade to Reloaded if you have not already. View <a href="%s" target="_blank">this support article</a> for more information.',
						'Postmatic'
					),
					'http://docs.gopostmatic.com/article/135-migrating-subscribers-from-subscribe-to-comments-reloaded'
				)
			)
		);

		if ( false === $this->table_exists() ) {
			return $content . $this->unavailable_content();
		}

		if ( $this->current_import_type == $this->import_type ) {
			return $content . $this->import_content();
		}

		return $content . $this->ready_content();
	}

	protected function unavailable_content() {
		$content = html( 'div id="subscribe-reloaded-unavailable"',
			html( 'p',
				__(
					'If you would like to import from Subscribe To Comments Reloaded  please activate that plugin.',
					'Postmatic'
				)
			)
		);
		return $content;
	}

	protected function import_content() {
		$import = new Prompt_Admin_Subscribe_Reloaded_Import();

		$import->execute();

		$content = html( 'h3', __( 'Here\'s how it went:', 'Postmatic' ) );

		$content .= $import->get_error() ? $import->get_error()->get_error_message() : '';

		$results_format = _n(
			'We have migrated one subscription.<br />',
			'We have migrated %1$s subscriptions.<br />',
			$import->get_imported_count(),
			'Postmatic'
		);

		$results_format .= __( 'You can disable Subscribe to Comments Reloaded at this time.<br />', 'Postmatic' );

		if ( $import->get_already_subscribed_count() > 0 ) {
			$results_format .= ' ' . _n(
				'There was one other subscription found but it had already been imported.',
				'There were %2$s other subscriptions found but they had already been imported.',
				$import->get_already_subscribed_count(),
				'Postmatic'
			);
		}

		$content = html( 'p',
			$content,
			sprintf(
				$results_format,
				$import->get_imported_count(),
				$import->get_already_subscribed_count()
			)
		);

		if ( true == $import->get_done() ) {
			Prompt_Core::$options->set( 'scr_import_done', true );
		}

		if ( false == $import->get_done() ) {
			$content .= html( 'p',
				__(
					sprintf(
						'For performance reasons, we only import %1d subscribers at a time. You have %2d possible subscribers remaining to import. Run the import again to import the next batch.',
						$import->get_batch_size(),
						$import->get_remaining()
					),
					'Postmatic'
				)
			);

			$content .= html( 'p', $this->ready_content() );
		}

		return $content;
	}

	protected function ready_content() {



		$content = '';

		$content .= $this->send_login_warning_content();


		$content .= html( 'input',
			array( 'name' => $this->import_type_name, 'type' => 'hidden', 'value' => $this->import_type )
		);

		return $this->form_wrap( $content, array( 'value' => __( 'Import from Subscribe To Comments Reloaded', 'Postmatic' ) ) );
	}


	protected function table_exists() {
		global $wpdb;
		$table_name = sprintf( '%ssubscribe_reloaded_subscribers', $wpdb->prefix );
		if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			return false;
		}
		return true;
	}

	protected function is_importable_comments_plugin_active() {
		// Check for Subscribe to Comments, StC Reloaded, or StC double opt-in
		return (
			class_exists( 'wp_subscribe_reloaded' ) or
			class_exists( 'CWS_STC' ) or
			class_exists( 'sg_subscribe' )
		);
	}

}
