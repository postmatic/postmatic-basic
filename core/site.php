<?php

/**
 * A subscription list for new posts on the site.
 *
 * @since 1.0.0
 */
class Prompt_Site extends Prompt_Option_Subscribable_Object {

	/** @type int */
	protected $id;
	/** @type  array */
	protected $html_phrases;
	/** @type  array */
	protected $text_phrases;

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function option_key() {
		return 'prompt_subscribed_user_ids';
	}

	/**
	 * @since 2.0.0
	 * @param $id
	 */
	public function __construct( $id = null ) {
		$this->id = is_null( $id ) ? get_current_blog_id() : $id;
	}

	/**
	 * @since 1.0.0
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function subscription_url() {
		return get_home_url();
	}

	/**
	 * @since 2.0.0 Added format parameter
	 * @since 1.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	public function subscription_object_label( $format = Prompt_Enum_Content_Types::HTML ) {
		/* translators: %s is the site name */
		$label = sprintf( __( 'new posts from %s', 'Postmatic' ), get_bloginfo( 'name' ) );
		return Prompt_Content_Handling::html_or_reduced_utf8( $format, $label );
	}

	/**
	 * @since 2.0.0 Added format parameter
	 * @since 1.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	public function subscription_description( $format = Prompt_Enum_Content_Types::HTML ) {
		return Prompt_Content_Handling::html_or_reduced_utf8(
			$format,
			sprintf(
				__(
					'You have successfully subscribed to %s and will receive new posts as soon as they are published.',
					'Postmatic'
				),
				get_option( 'blogname' )
			)
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function subscribe_phrase() {
		return Prompt_Instant_Matcher::target();
	}

	/**
	 * @since 2.0.0
	 * @param string $text
	 * @return string
	 */
	public function matches_subscribe_phrase( $text ) {
		$matcher = new Prompt_Instant_Matcher( $text );
		return $matcher->matches();
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
					'To get new posts as soon as they are published, reply with the word \'%s\'.',
					'Postmatic'
				),
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
		return __( 'Please send all new posts to me by email.', 'Postmatic' );
	}

	/**
	 * @since 1.0.0
	 * @param int $user_id
	 * @return array
	 */
	public static function subscribed_object_ids( $user_id ) {
		$ids = array();
		$site = new Prompt_Site;
		if ( $site->is_subscribed( $user_id ) )
			$ids[] = $site->id();
		return $ids;
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	public static function all_subscriber_ids() {
		// Currently just the default site subscribers
		$site = new Prompt_Site;
		return $site->subscriber_ids();
	}

}