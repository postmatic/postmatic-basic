<?php

/**
 * Post delivery options tab
 *
 * @since 2.0.0
 *
 */
class Prompt_Admin_Post_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Configure Posts', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'post-delivery';
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function render() {
		$introduction = html(
			'div class="intro-text"',
			html( 'h2', __( 'Send Posts to Subscribers', 'Postmatic' ) ),
			html( 'p', __( 'Posts are sent as soon as you hit publish.<br /> Comments can be sent with a simple reply.', 'Postmatic' ) )
		);

		return $introduction . $this->form_table( $this->table_entries() );
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

		$valid_data = $this->validate_checkbox_fields(
			$new_data,
			$old_data,
			array(
				'send_login_info',
				'auto_subscribe_authors',
				'no_post_email_default',
				'excerpt_default',
			)
		);

		return $valid_data;
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
	 * The table entries for the form.
	 * @since 2.0.0
	 * @return array
	 */
	protected function table_entries() {
		
		$table_entries = array(
			array(
				'title' => __( 'Author Subscriptions', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'auto_subscribe_authors',
				'desc' => __(
						'Subscribe authors to comments on their own posts.<small>(Recommended)</small>',
						'Postmatic'
					) . html( 'p',
						__(
							'This will automatically subscribe post authors to new comment notifications on their posts. This works well to keep the author up to date with the latest comments and discussion.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'User Accounts', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'send_login_info',
				'desc' => __( 'Email subscribers WordPress account credentials when they subscribe.', 'Postmatic' ) .
					html( 'p',
						__(
							'Only necessary in some situations as all user commands are otherwise possible via email. If enabled we recommend using a good front end login plugin.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'Choose Posts to Deliver', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'no_post_email_default',
				'desc' => __( 'Do not send new posts unless I choose to.', 'Postmatic' ) .
					html( 'p',
						__(
							'Uncheck the "Do not deliver this post via email" checkbox before publishing to deliver a specific post.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'Default sending mode', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'excerpt_default',
				'desc' => __( 'Send only the excerpt instead of the full post content.', 'Postmatic' ) .
					html( 'p',
						__(
							'Enable this setting to only send excerpts with a button to read more online. You can override this on a per-post basis when drafting a new post. Note that the user will not be able to reply to the email to leave a comment because... well... who can comment on an excerpt?',
							'Postmatic'
						)
					),
			),
		);

		$this->override_entries( $table_entries );

		return $table_entries;
	}
}