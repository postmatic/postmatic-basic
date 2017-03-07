<?php

/**
 * Comment delivery options tab
 * @since 2.0.0
 */

class Prompt_Admin_Comment_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function name() {
		return __( 'Comment Subscription Options', 'Postmatic' );
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function slug() {
		return 'comment-delivery';
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function render() {
		return html(
			'div class="intro-text"',
			html( 'h2', __( 'Choose how comment subscriptions work', 'Postmatic' ) ),
			$this->form_table( $this->table_entries() )
		);
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
	 *
	 * @since 2.0.0
	 *
	 * @param array $new_data
	 * @param array $old_data
	 * @return array
	 */
	function validate( $new_data, $old_data ) {

		$valid_data = $this->validate_checkbox_fields(
			$new_data,
			$old_data,
			array( 'comment_opt_in_default', 'comment_snob_notifications', 'auto_subscribe_authors' )
		);

		if ( isset( $new_data['comment_opt_in_text'] ) ) {
			$valid_data['comment_opt_in_text'] = sanitize_text_field( $new_data['comment_opt_in_text'] );
		}

		$flood_trigger_count = $new_data['comment_flood_control_trigger_count'];
		$flood_trigger_count = is_numeric( $flood_trigger_count ) ? absint( $flood_trigger_count ) : 6;
		$flood_trigger_count = ( $flood_trigger_count < 2 ) ? 2 : $flood_trigger_count;
		$valid_data['comment_flood_control_trigger_count'] = $flood_trigger_count;

		return $valid_data;
	}

	/**
	 * Check if Elevated Comments is active
	 * @since 2.0.0
	 * @return bool
	 */
	protected function is_elevated_active() {
		return class_exists( 'CommentIQ_Plugin' );
	}

	/**
	 * @since 2.0.6
	 * @return array
	 */
	 

	protected function table_entries() {
	
		$snob_extra = array();
		$snob_upgrade_link = '';

		if ( ! $this->is_comment_digest_message_type_enabled() ) {
			$snob_extra['class'] = 'disabled';
			$snob_extra['disabled'] = 'disabled';
			$snob_upgrade_link = $this->upgrade_link();
		}

		$table_entries = array(
			array(
				'title' => __( 'Comment form opt-in', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'comment_opt_in_default',
				'desc' => __( 'Subscribe commenters to the conversation by default.', 'Postmatic' ) .
					html( 'p',
						__(
							'Please note this may place you in violation of European and Canadian spam laws. Be sure to do your homework.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'Comment form opt-in text', 'Postmatic' ),
				'type' => 'text',
				'name' => 'comment_opt_in_text',
				'desc' => __( 'This text is displayed by the checkbox on the comment form.', 'Postmatic' ),
				'extra' => array( 'class' => 'regular-text last-submit' ),
			),
			array(
				'title' => __( 'Comment flood control', 'Postmatic' ),
				'type' => 'text',
				'name' => 'comment_flood_control_trigger_count',
				'desc' => __( 'How many comments in one hour should it take to trigger flood control? There is a mimimum of 2 (one comment, one reply).', 'Postmatic' ) .
					html( 'p',
						sprintf(
							__(
								'Replyable automatically pauses comment notifications on posts that go viral. Setting the trigger to be 6 comments per hour is good for most sites. You can read more about it <a href="%s" target="_blank">on our support site</a>.  ',
								'Postmatic'
							),
							'http://docs.replyable.com/article/275-what-happens-if-a-post-gets-a-gazillion-comments-do-i-get-a-gazillion-emails'
						)
					),
				'extra' => array( 'size' => 3 ),
			),
			array(
				'title' => __( 'Comment Intelligence', 'Postmatic' ) . ' ' . $snob_upgrade_link,
				'type' => 'checkbox',
				'name' => 'comment_snob_notifications',
				'desc' => __( 'Only email comments which contain a high level of relevance to the post and conversation.', 'Postmatic' ) .
					html( 'p',
						sprintf(
							__(
								'We\'ll analyze each comment, identify those worth sending, and hold on to the rest for the daily comment digest. This keeps short comments, nonsense, and any potential spam from bothering subscribers. Direct replies will still be sent to the person being responded to so the conversation keeps growing.',
								'Postmatic'
							),
							'http://elevated.gopostmatic.com'
						)
					),
				'extra' => $snob_extra,
			),
		);

		$this->override_entries( $table_entries );

		return $table_entries;
	}
}
