<?php

/**
 * List for all comments sitewide.
 *
 * @since 1.0.0
 *
 */
class Prompt_Site_Comments extends Prompt_Option_Subscribable_Object {

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function option_key() {
		return 'prompt_comments_subscribed_user_ids';
	}

	/**
	 * @since 1.0.0
	 * @return int
	 */
	public function id() {
		return get_current_blog_id();
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function subscription_url() {
		return get_edit_profile_url();
	}

	/**
	 * A title.
	 *
	 * @since 2.0.0 Added format parameter
	 * @since 1.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	public function subscription_object_label( $format = Prompt_Enum_Content_Types::HTML ) {
		return Prompt_Content_Handling::html_or_reduced_utf8(
			$format,
			sprintf( __( 'all comments on %s', 'Postmatic' ), get_option( 'blogname' ) )
		);
	}

	/**
	 * @since 2.0.0 Added format parameter
	 * @since 1.0.0
	 *
	 * @param string $format
	 * @return string
	 */
	public function subscription_description( $format = Prompt_Enum_Content_Types::HTML ) {
		return Prompt_Content_Handling::html_or_reduced_utf8(
			$format,
			sprintf(
				__(
					'You have successfully subscribed to all comments on %s and will receive new comments as soon as they are published.',
					'Postmatic'
				),
				get_option( 'blogname' )
			)
		);
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	public function select_reply_prompt( $format = Prompt_Enum_Content_Types::HTML ) {
		$subscribe_mailto = sprintf(
			'mailto:{{{reply_to}}}?subject=%s&body=%s',
			__( 'Just hit send', 'Postmatic' ),
			$this->subscribe_phrase()
		);
		return Prompt_Content_Handling::html_or_reduced_utf8(
			$format,
			sprintf(
				__(
					'To get all new comments sitewide on %1$s, reply with the word \'%2$s\'.',
					'Postmatic'
				),
				get_option( 'blogname' ),
				"<a href=\"$subscribe_mailto\">{$this->subscribe_phrase()}</a>"
			)
		);
	}

	/**
	 * @since 2.0.0
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	public function subscribe_prompt( $format = Prompt_Enum_Content_Types::HTML ) {
		return __(
			'Please send me all new comments by email. Even ones on posts to which I have not subscribed.',
			'Postmatic'
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function subscribe_phrase() {
		return __( 'sitewide comments', 'Postmatic' );
	}

	/**
	 * @since 2.0.0
	 * @param string $text
	 * @return string
	 */
	public function matches_subscribe_phrase( $text ) {
		return ( $text == $this->subscribe_phrase() );
	}

	/**
	 * @since 1.0.0
	 * @param int $user_id
	 * @return array
	 */
	public static function subscribed_object_ids( $user_id ) {
		$ids = array();
		$site = new Prompt_Site_Comments();
		if ( $site->is_subscribed( $user_id ) )
			$ids[] = $site->id();
		return $ids;
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	public static function all_subscriber_ids() {
		// Currently just the default site comments subscribers
		$site = new Prompt_Site_Comments();
		return $site->subscriber_ids();
	}

}