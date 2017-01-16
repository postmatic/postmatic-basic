<?php

/**
 * Prompt behavior specific to a post.
 *
 * Encapsulates a WordPress post, since WordPress doesn't allow extension.
 *
 * @since 2.0.0 Subscribable interface updates
 * @since 1.0.0
 *
 */
class Prompt_Post extends Prompt_Meta_Subscribable_Object {

	/** @var string */
	protected static $sent_meta_key = 'prompt_sent_ids';
	/** @var string */
	protected static $failed_meta_key = 'prompt_failed_ids';
	/** @var string */
	protected static $recipient_ids_meta_key = 'prompt_recipient_ids';
	/** @var string */
	protected static $flood_control_meta_key = '_flood_control_comment_id';
	/** @var string */
	protected static $text_version_meta_key = '_prompt_text_version';
	/** @var string */
	protected static $outbound_message_batch_ids_meta_key = '_prompt_outbound_message_batch_ids';
	/** @var string */
	protected static $custom_html_meta_key = '_prompt_custom_html';

	/** @var  int user ID */
	protected $id;
	/** @var WP_Post post object */
	protected $wp_post;

	/**
	 * Create a Prompt post.
	 *
	 * @since 1.0.0
	 *
	 * @param int|WP_Post $post_id_or_object
	 */
	public function __construct( $post_id_or_object ) {

		$this->meta_type = 'post';

		if ( is_a( $post_id_or_object, 'WP_Post' ) ) {
			$this->wp_post = $post_id_or_object;
			$this->id = $this->wp_post->ID;
		} else {
			$this->id = intval( $post_id_or_object );
		}
	}

	/**
	 * Get the WordPress user ID.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Get the underlying post.
	 *
	 * @since 1.0.0
	 * @return null|WP_Post
	 */
	public function get_wp_post() {
		if ( !isset( $this->wp_post ) )
			$this->wp_post = get_post( $this->id );
		return $this->wp_post;
	}

	/**
	 * Get a post excerpt without having to set up globals.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_excerpt() {
		$excerpt = $this->get_wp_post()->post_excerpt;

		if ( empty( $excerpt ) ) {
			$text = apply_filters( 'the_content', $this->get_wp_post()->post_content );
			$excerpt = wp_trim_words( $text, 55, '[&hellip;]' );
		}

		return $excerpt;
	}

	/**
	 * @since 1.0.0
	 * @return string The customized post text, empty if none was set.
	 */
	public function get_custom_text() {
		return get_post_meta( $this->id, self::$text_version_meta_key, true );
	}

	/**
	 * @since 1.0.0
	 * @param string $text The customized post text.
	 * @return Prompt_Post $this
	 */
	public function set_custom_text( $text ) {
		update_post_meta( $this->id, self::$text_version_meta_key, $text );
		return $this;
	}

	/**
	 * @since 2.0.0
	 * @return string The customized post HTML, empty if none was set.
	 */
	public function get_custom_html() {
		return get_post_meta( $this->id, self::$custom_html_meta_key, true );
	}

	/**
	 * @since 2.0.0
	 * @param string $html The customized post text.
	 * @return Prompt_Post $this
	 */
	public function set_custom_html( $html ) {
		update_post_meta( $this->id, self::$custom_html_meta_key, $html );
		return $this;
	}

	/**
	 * Use the post permalink as the subscription URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function subscription_url() {
		return get_permalink( $this->id );
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
			sprintf( __( 'discussion of <em>%s</em>', 'Postmatic' ), $this->get_wp_post()->post_title )
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
		/* translators: %s is the post title*/
		return Prompt_Content_Handling::html_or_reduced_utf8(
			$format,
			sprintf(
				__(
					'You have successfully subscribed and will receive an email when there is a new comment on <em>%s</em>.',
					'Postmatic'
				),
				$this->get_wp_post()->post_title
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
					'To get all new comments on %1%s, reply with the word \'%2$s\'</a>.',
					'Postmatic'
				),
				$this->get_wp_post()->post_title,
				"<a href=\"$subscribe_mailto\">{$this->subscribe_phrase()}</a>"
			)
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function subscribe_phrase() {
		return __( 'post', 'Postmatic' ) . '-' . $this->get_wp_post()->ID;
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
	 * Return cached recipient ids for published posts, otherwise clear cached recipients.
	 *
	 * When a post is unpublished, this allows recipients to change when published again.
	 *
	 * @return array|mixed
	 */
	protected function cached_recipient_ids() {
		if ( 'publish' == $this->get_wp_post()->post_status )
			return get_post_meta( $this->id, self::$recipient_ids_meta_key, true );

		// Clear cache for unpublished posts
		delete_post_meta( $this->id, self::$recipient_ids_meta_key );

		return array();
	}

	/**
	 * Get the IDs of users who should receive an email when this post is published.
	 *
	 * This includes both subscribers to the author and to the site.
	 *
	 * Post types not enabled in the options will have no recipients.
	 *
	 * @since 2.1.0 Site subscriptions removed, new posts have no recipients by default
	 *
	 * @return array An array of user IDs.
	 */
	public function recipient_ids() {

		$post = $this->get_wp_post();

		if ( !in_array( $post->post_type, Prompt_Core::$options->get( 'site_subscription_post_types' ) ) ) {
			return array();
		}

		$recipient_ids = $this->cached_recipient_ids();

		if ( !$recipient_ids ) {

			$prompt_author = new Prompt_User( $post->post_author );
			$recipient_ids = $prompt_author->subscriber_ids();

			/**
			 * Filter the recipient ids of notifications for a post.
			 *
			 * @param array   $recipient_ids
			 * @param WP_Post $post
			 */
			$recipient_ids = apply_filters( 'prompt/recipient_ids/post', $recipient_ids, $post );

			if ( 'publish' == $post->post_status ) {
				update_post_meta( $post->ID, self::$recipient_ids_meta_key, $recipient_ids );
			}

		}

		return $recipient_ids;
	}

	/**
	 * Get an array of items from a meta field.
	 *
	 * @since 2.0.14
	 * @param string $key
	 * @return array
	 */
	protected function get_array_meta( $key ) {
		$items = get_post_meta( $this->id, $key, true );

		if ( !$items )
			$items = array();

		return $items;
	}

	/**
	 * Get the IDs of users who have been sent an email notification for this post.
	 *
	 * @return array
	 */
	public function sent_recipient_ids() {
		return $this->get_array_meta( self::$sent_meta_key );
	}

	/**
	 * Add the IDs of users who have been sent an email notification for this post.
	 *
	 * @param array $ids
	 * @return $this
	 */
	public function add_sent_recipient_ids( $ids ) {
		$sent_ids = array_unique( array_merge( $this->sent_recipient_ids(), $ids ) );
		update_post_meta( $this->id, self::$sent_meta_key, $sent_ids );
		return $this;
	}

	/**
	 * Remove the IDs of users for whom an email notification mailing failed.
	 *
	 * @param array $ids
	 * @return $this
	 */
	public function remove_sent_recipient_ids( $ids ) {
		$sent_ids = array_diff( $this->sent_recipient_ids(), $ids );
		update_post_meta( $this->id, self::$sent_meta_key, $sent_ids );
		return $this;
	}

	/**
	 * Get the IDs of users for whom the email notification for this post failed.
	 *
	 * @since 2.0.14
	 * @return array
	 */
	public function failed_recipient_ids() {
		return $this->get_array_meta( self::$failed_meta_key );
	}

	/**
	 * Add the IDs of users for whom the email notification for this post failed.
	 *
	 * @since 2.0.14
	 * @param array $ids
	 * @return $this
	 */
	public function add_failed_recipient_ids( $ids ) {
		$failed_ids = array_unique( array_merge( $this->failed_recipient_ids(), $ids ) );
		update_post_meta( $this->id, self::$failed_meta_key, $failed_ids );
		return $this;
	}

	/**
	 * Remove the IDs of users for whom an email notification mailing failed.
	 *
	 * @since 2.0.14
	 * @param array $ids
	 * @return $this
	 */
	public function remove_failed_recipient_ids( $ids ) {
		$failed_ids = array_diff( $this->failed_recipient_ids(), $ids );
		update_post_meta( $this->id, self::$failed_meta_key, $failed_ids );
		return $this;
	}

	/**
	 * Get the IDs of users who have been NOT yet been sent an email notification for this post.
	 *
	 * @return array
	 */
	public function unsent_recipient_ids() {
		return array_diff( $this->recipient_ids(), $this->sent_recipient_ids() );
	}

	/**
	 * Get the IDs of outbound message batches requested for this post.
	 *
	 * @return array
	 */
	public function outbound_message_batch_ids() {
		return $this->get_array_meta( self::$outbound_message_batch_ids_meta_key );
	}

	/**
	 * Add the IDs of outbound message batches requested for this post.
	 *
	 * @param array|int $ids
	 * @return $this
	 */
	public function add_outbound_message_batch_ids( $ids ) {
		$ids = is_array( $ids ) ? $ids : array( $ids );
		$sent_ids = array_unique( array_merge( $this->outbound_message_batch_ids(), $ids ) );
		update_post_meta( $this->id, self::$outbound_message_batch_ids_meta_key, $sent_ids );
		return $this;
	}

	/**
	 * Get the comment ID that triggered flood control on this post.
	 *
	 * @return int Comment ID or 0 for none.
	 */
	public function get_flood_control_comment_id() {
		return intval( get_post_meta( $this->id, self::$flood_control_meta_key, true ) );
	}

	/**
	 * @param int $comment_id
	 * @return $this
	 */
	public function set_flood_control_comment_id( $comment_id ) {
		update_post_meta( $this->id, self::$flood_control_meta_key, $comment_id );
		return $this;
	}

	/**
	 * Get all the posts a user is subscribed to.
	 *
	 * @param $user_id
	 * @return mixed|void
	 */
	public static function subscribed_object_ids( $user_id ) {

		// Using a "fake" post object for PHP 5.2, which doesn't have static method inheritance
		$post = new Prompt_Post( 0 );

		return $post->_subscribed_object_ids( $user_id );
	}

	/**
	 * Get the IDs of all users subscribed to at least one post.
	 *
	 * @return array
	 */
	public static function all_subscriber_ids() {

		// Using a "fake" object for PHP 5.2, which doesn't have static method inheritance
		$prompt_post = new Prompt_Post( 0 );

		return $prompt_post->_all_subscriber_ids();
	}

	/**
	 * Get a meta query clause to select posts that have been sent out.
	 *
	 * @return array
	 */
	public static function sent_posts_meta_clause() {
		return array(
			'key' => self::$sent_meta_key,
			'compare' => 'EXISTS',
		);
	}
}