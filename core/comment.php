<?php

/**
 * Prompt behavior specific to a comment.
 *
 * Encapsulates a WordPress comment, since WordPress doesn't allow extension.
 *
 * @since 2.0.0
 */
class Prompt_Comment {

	/** @var string */
	protected static $recipient_ids_meta_key = 'prompt_recipient_ids';
	/** @var string */
	protected static $sent_meta_key = 'prompt_sent_ids';
	/** @var string */
	protected static $failed_meta_key = 'prompt_failed_ids';
	/** @var string */
	protected static $outbound_message_batch_ids_meta_key = 'prompt_outbound_message_batch_ids';
	/** @var string */
	protected static $subscription_requested_meta_key = 'prompt_comment_subscribe';

	/** @var  int comment ID */
	protected $id;
	/** @var object|WP_Comment comment object */
	protected $wp_comment;
	/** @var  WP_User */
	protected $author_user;

	/**
	 * Create a Prompt comment.
	 *
	 * @since 2.0.0
	 *
	 * @param int|object|WP_Comment $comment_id_or_object
	 */
	public function __construct( $comment_id_or_object ) {
		if ( is_object( $comment_id_or_object ) ) {
			$this->wp_comment = $comment_id_or_object;
			$this->id = $this->wp_comment->comment_ID;
		} else {
			$this->id = intval( $comment_id_or_object );
		}
	}

	/**
	 * Get the WordPress comment ID.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Get the underlying comment.
	 *
	 * @since 1.0.0
	 * @return null|object|WP_Comment
	 */
	public function get_wp_comment() {
		if ( !isset( $this->wp_comment ) ) {
			$this->wp_comment = get_comment( $this->id );
		}
		return $this->wp_comment;
	}

	/**
	 * @since 2.0.0
	 * @return array
	 */
	public function get_recipient_ids() {
		$ids = get_comment_meta( $this->id, self::$recipient_ids_meta_key, true );
		return $ids ? $ids : array();
	}

	/**
	 * @since 2.0.0
	 * @param array $ids
	 * @return $this
	 */
	public function set_recipient_ids( array $ids ) {
		update_comment_meta( $this->id, self::$recipient_ids_meta_key, $ids );
		return $this;
	}
	
	/**
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sent_subscriber_ids() {
		$ids = get_comment_meta( $this->id, self::$sent_meta_key, true );
		return $ids ? $ids : array();
	}

	/**
	 * @since 2.0.0
	 * @param array $ids
	 * @return $this
	 */
	public function set_sent_subscriber_ids( array $ids ) {
		update_comment_meta( $this->id, self::$sent_meta_key, $ids );
		return $this;
	}

	/**
	 * @since 2.0.14
	 * @return array
	 */
	public function get_failed_subscriber_ids() {
		$ids = get_comment_meta( $this->id, self::$failed_meta_key, true );
		return $ids ? $ids : array();
	}

	/**
	 * @since 2.0.14
	 * @param array $ids
	 * @return $this
	 */
	public function set_failed_subscriber_ids( array $ids ) {
		update_comment_meta( $this->id, self::$failed_meta_key, $ids );
		return $this;
	}

	/**
	 * @since 2.0.14
	 * @param array $ids
	 * @return $this
	 */
	public function add_failed_subscriber_ids( array $ids ) {
		return $this->set_failed_subscriber_ids( array_unique( array_merge( $this->get_failed_subscriber_ids(), $ids ) ) );
	}

	/**
	 * @since 2.0.14
	 * @param array $ids
	 * @return $this
	 */
	public function remove_failed_subscriber_ids( array $ids ) {
		return $this->set_failed_subscriber_ids( array_diff( $this->get_failed_subscriber_ids(), $ids ) );
	}

	/**
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sent_batch_ids() {
		$ids = get_comment_meta( $this->id, self::$outbound_message_batch_ids_meta_key, true );
		return $ids ? $ids : array();
	}

	/**
	 * @since 2.0.0
	 * @param int $id
	 * @return $this
	 */
	public function add_sent_batch_id( $id ) {
		$ids = $this->get_sent_batch_ids();
		$ids[] = intval( $id );
		update_comment_meta( $this->id, self::$outbound_message_batch_ids_meta_key, $ids );
		return $this;
	}

	/**
	 * @since 2.0.0
	 * @return $this
	 */
	public function set_subscription_requested() {
		update_comment_meta( $this->id, self::$subscription_requested_meta_key, true );
		return $this;
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	public function get_subscription_requested() {
		return (bool) get_comment_meta( $this->id, self::$subscription_requested_meta_key, true );
	}
	
	/**
	 * Get the comment author user if there is one.
	 *
	 * @since 2.0.0
	 *
	 * @return bool|WP_User Author user or false if none is found.
	 */
	public function get_author_user() {

		if ( $this->author_user ) {
			return $this->author_user;
		}
		
		$user = get_user_by( 'id', $this->get_wp_comment()->user_id );
		
		$this->author_user = $user ? $user : get_user_by( 'email', $this->get_wp_comment()->comment_author_email );
		
		return $this->author_user;
	}

	/**
	 * Get the author user display name, comment form author name, or Anonymous.
	 * @since 2.0.0
	 * @return string
	 */
	public function get_author_name() {
		$author = $this->get_author_user();
		
		if ( $author ) {
			return $author->display_name;
		}
		
		$name_from_form =$this->get_wp_comment()->comment_author;  
		
		return $name_from_form ? $name_from_form : __( 'Anonymous', 'Postmatic' );
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	public function author_can_subscribe() {
		return (bool) ( $this->get_wp_comment()->user_id or is_email( $this->get_wp_comment()->comment_author_email ) );
	}
}