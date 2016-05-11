<?php

/**
 * MailPoet import UI
 *
 * @package Postmatic
 * @since   1.0.0
 */
class Prompt_Admin_Mailpoet_Import_Options_Tab extends Prompt_Admin_Import_Options_Tab {

	/**
	 * @var string
	 */
	protected $import_list_name = 'import_list';
	/**
	 * @var string
	 */
	protected $rejected_addresses_name = 'rejected_addresses';
	/**
	 * @var string
	 */
	protected $import_type = 'mailpoet_import';

	/**
	 * Tab name.
	 *
	 * @since 1.0.0
	 * @return string|void
	 */
	public function name() {
		return __( 'MailPoet Import', 'Postmatic' );
	}

	/**
	 * Tab slug.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function slug() {
		return 'mailpoet-import';
	}

	/**
	 * Get tab content.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function render() {
		$content = html( 'h2', __( 'Mailpoet Import', 'Postmatic' ) );

		if ( ! class_exists( 'WYSIJA' ) )
			return $content . $this->unavailable_content();

		if ( $this->current_import_type == $this->import_type )
			return $content . $this->import_content();

		return $content . $this->ready_content();
	}

	/**
	 * Message content if MailPoet isn't found.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function unavailable_content() {
		$content = html( 'div id="mailpoet-unavailable"',
			html( 'p',
				__(
					'If you would like to import Mailpoet users please activate the Mailpoet plugin.',
					'Postmatic'
				)
			)
		);
		return $content;
	}

	/**
	 * Get import results in HTML.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function import_content() {

		$list_id = intval( $_POST[$this->import_list_name] );
		$signup_list_index = intval( $_POST['signup_list_index'] );

		$signup_lists = Prompt_Subscribing::get_signup_lists();
		$signup_list = $signup_lists[$signup_list_index];

		$import = Prompt_Admin_Mailpoet_Import::make( $list_id, $signup_list );

		$import->execute();

		$content = html( 'h3', __( 'Here\'s how it went:', 'Postmatic' ) );

		$content .= $import->get_error() ? $import->get_error()->get_error_message() : '';

		$results_format = _n(
			'Imported one subscriber.',
			'Imported %1$s subscribers.',
			$import->get_imported_count(),
			'Postmatic'
		);

		if ( $import->get_already_subscribed_count() > 0 ) {
			$results_format .= ' ' . _n(
				'The one valid user we found was already subscribed.',
				'The %2$s valid users we found were already subscribed.',
				$import->get_already_subscribed_count(),
				'Postmatic'
			);
		}

		$rejects = $import->get_rejected_subscribers();
		$reject_content = '';
		$reject_button = '';
		if ( $rejects ) {

			$results_format .= '<br />' . _n(
				'One user didn\'t qualify for importing.',
				'There were %3$s users which didn\'t qualify for importing.',
				count( $rejects )
			);

			$reject_content = html( 'div id="mailpoet-import-intro"',
				html( 'div',
					html( 'h4', __( 'Why weren\'t more of my users imported?', 'Postmatic' ) ),
					html( 'p',
						__(
							'We have a very strict policy regarding user imports: <em>we will never allow anyone to be subscribed to a blog running Postmatic without them having opted in</em> (such as subscriber lists bought and imported in bulk for spamming). Because of this we will not import any Mailpoet subscribers unless the following two conditions are true:',
							'Postmatic'
						)
					),
					html( 'ol',
						html( 'li', __( 'The user has opened an email you sent through Mailpoet', 'Postmatic' ) ),
						html( 'li', __( 'The user has clicked a link within an email you sent through Mailpoet', 'Postmatic' ) )
					),
					html( 'h5', __( 'Why so strict?', 'Postmatic' ) ),
					html( 'p',
						__(
							'Bulk importing unwilling users and marking them as opted-in is easy in Mailpoet. If we did not hold our import to a higher standard the magic button below would allow those unwilling users to be imported into Postmatic. And then they would spam your grandmother. Nobody wants that. Plus, if a subscriber does not open or interact with your emails maybe they aren\'t all that good of a match anyway, right? Think of it as spring cleaning.',
							'Postmatic'
						)
					),
					html( 'h4', __( 'But we do have good news', 'Postmatic' ) ),
					html( 'p',
						__(
							'You can send an email to your remaining users. They will be invited to join your site by simply replying.',
							'Postmatic'
						)
					)
				)
			);

			$rejected_addresses = array();
			foreach ( $rejects as $reject ) {
				$name = trim( $reject['firstname'] . ' ' . $reject['lastname'] );
				$rejected_addresses[] = Prompt_Email_Batch::name_address( $reject['email'], $name );
			}

			$reject_button = html( 'input',
				array(
					'name' => $this->rejected_addresses_name,
					'class' => 'button',
					'data-addresses' => implode( ",", $rejected_addresses ),
					'type' => 'submit',
					'value' => __( 'Preview and send the invitations', 'Postmatic' ),
				)
			);
		}

		$content = html( 'p',
			$content,
			sprintf(
				$results_format,
				$import->get_imported_count(),
				$import->get_already_subscribed_count(),
				count( $rejects )
			),
			$reject_content,
			$reject_button
		);

		return $content;
	}

	/**
	 * The HTML for the UI to start the import.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function ready_content() {

		$mailpoet_lists = $this->get_mailpoet_lists();

		if ( count( $mailpoet_lists ) === 0 )
			return html( 'div id="mailpoet-no-subscribers',
				__( 'There are no lists available from MailPoet. Are you sure it is activated?', 'Postmatic' )
			);

		$active_subscriber_text = __(
			'Mailpoet is detected. We are ready to import active subscribers from Mailpoet.',
			'Postmatic'
		);

		$list_options = array();
		foreach ( $mailpoet_lists as $list ) {
			$list_options[] = html( 'option',
				array( 'value' => $list['list_id'] ),
				$list['name'],
				' (',
				$list['subscribers'],
				')'
			);
		}

		$local_list_options = array();
		foreach ( Prompt_Subscribing::get_signup_lists() as $index => $list ) {
			$local_list_options[] = html( 'option',
				array( 'value' => $index ),
				$list->subscription_object_label()
			);
		}

		$html_parts = array();
		$html_parts[] = html( 'div id="mailpoet-import-intro"',
			html( 'div',
				html( 'h3', __( 'Mailpoet Import FAQ', 'Postmatic' ) ),
				html( 'h4', __( 'Will my subscribers be sent a notification?', 'Postmatic' ) ),
				html( 'p', __( 'No. The import process is invisible to subscribers.', 'Postmatic' ) ),
				html( 'h4', __( 'Which of my subscribers will be imported?', 'Postmatic' ) ),
				html( 'p',
					__(
						'We have a very strict policy regarding user imports: <em>we will never allow anyone to be subscribed to a blog running Postmatic without them having opted in</em> (such as subscriber lists bought and imported in bulk for spamming). Because of this we will not import any Mailpoet subscribers unless the following two conditions are true:',
						'Postmatic'
					)
				),
				html( 'ol',
					html( 'li', __( 'The user has opened an email you sent through Mailpoet', 'Postmatic' ) ),
					html( 'li', __( 'The user has clicked a link within an email you sent through Mailpoet', 'Postmatic' ) )
				),
				html( 'h5', __( 'Why so strict?', 'Postmatic' ) ),
				html( 'p',
					__(
						'Bulk importing unwilling users and marking them as opted-in is easy in Mailpoet. If we did not hold our import to a higher standard the magic button below would allow those unwilling users to be imported into Postmatic. And then they would spam your grandmother. Nobody wants that. Plus, if a subscriber does not open or interact with your emails maybe they aren\'t all that good of a match anyway, right? Think of it as spring cleaning.',
						'Postmatic'
					)
				),
				html( 'h4', __( 'Can I import multiple lists?', 'Postmatic' ) ),
				html( 'p',
					__(
						'Yes. Re-run this importer with as many lists as you like. Postmatic will not create duplicate subscribers.',
						'Postmatic'
					)
				),
				html( 'h4',
					__(
						'Does Postmatic have lists like Mailpoet does? Is there any way to organize subscribers?',
						'Postmatic'
					)
				),
				html( 'p',
					__(
						'No, we do not have a concept of multiple lists. All users are the same in Postmatic. If list segmentation is important to you please let us know by visiting our support site. You\'ll find the link to the right.',
						'Postmatic'
					)
				),
				html( 'h4', __( 'What will happen to my Mailpoet subscribers?', 'Postmatic' ) ),
				html( 'p',
					__(
						'Mailpoet and Postmatic store subscribers in different places within your WordPress database. Your Mailpoet subscribers will always be available to you provided you have Mailpoet activated.',
						'Postmatic'
					)
				)
			),
			$active_subscriber_text
		);

		$html_parts[] = $this->send_login_warning_content();

		$html_parts[] = html( 'label for="import_list"',
			__( 'Choose a Mailpoet list to import from: ', 'Postmatic' ),
			html( 'select',
				array( 'name' => 'import_list', 'type' => 'select' ),
				implode( '', $list_options )
			)
		);

		$html_parts[] = '<br/>';

		if ( count( $local_list_options ) > 1 ) {
			$html_parts[] = html( 'label id="mailpoet_signup_list_index_label" for="signup_list_index"',
				__( 'Choose a Postmatic list to import to:', 'Postmatic' ),
				' ',
				html( 'select',
					array( 'name' => 'signup_list_index', 'type' => 'select' ),
					implode( '', $local_list_options )
				)
			);
		} else {
			$html_parts[] = html( 'input',
				array( 'type' => 'hidden', 'name' => 'signup_list_index', 'value' => 0 )
			);
		}

		$html_parts[] = html( 'input',
			array( 'name' => $this->import_type_name, 'type' => 'hidden', 'value' => $this->import_type )
		);

		return $this->form_wrap(
			implode( '', $html_parts ),
			array( 'value' => __( 'Import from Mailpoet', 'Postmatic' ) )
		);
	}

	/**
	 * Get the available MailPoet lists.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_mailpoet_lists() {
		$lists = WYSIJA::get( 'list', 'model' )->getLists();
		$filtered_lists = array();
		foreach ( $lists as $list ) {
			if ( 'WordPress Users' != $list['name'] )
				$filtered_lists[] = $list;
		}
		return $filtered_lists;
	}

}