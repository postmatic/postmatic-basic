<?php

/**
 * Prompt behavior specific to a user.
 *
 * Encapsulates a WordPress user, since WordPress doesn't allow extension.
 *
 * @since 1.0.0
 */
class Prompt_User extends Prompt_Meta_Subscribable_Object {

	/** @var  int user ID */
	protected $id;
	/** @var WP_User user object */
	protected $wp_user;
	/** @var Prompt_Subscriber_Origin */
	protected $origin;
	/** @var string */
	protected $origin_meta_key = 'prompt_subscriber_origin';

	/** @type  array */
	protected static $subscribed_object_ids;

	/**
	 * Create an Prompt_Core user.
	 *
	 * @since 1.0.0
	 * @param int|WP_User $user_id_or_object
	 */
	public function __construct( $user_id_or_object ) {

		$this->meta_type = 'user';

		if ( is_a( $user_id_or_object, 'WP_User' ) ) {
			$this->wp_user = $user_id_or_object;
			$this->id = $this->wp_user->ID;
		} else {
			$this->id = intval( $user_id_or_object );
		}
	}

	/**x
	 * Get the WordPress user ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Get the underlying user.
	 * @since 1.0.0
	 * @return null|WP_User
	 */
	public function get_wp_user() {
		if ( !isset( $this->wp_user ) )
			$this->wp_user = get_userdata( $this->id );
		return $this->wp_user;
	}

	/**
	 * @since 1.0.0
	 * @return null|Prompt_Subscriber_Origin
	 */
	public function get_subscriber_origin() {
		if ( !isset( $this->origin ) ) {
			$origin = get_user_meta( $this->id, $this->origin_meta_key, true );
			$this->origin = $origin ? $origin : null;
		}
		return $this->origin;
	}

	/**
	 * @since 1.0.0
	 * @param Prompt_Subscriber_Origin $origin
	 * @return Prompt_User $this
	 */
	public function set_subscriber_origin( Prompt_Subscriber_Origin $origin ) {
		$this->origin = $origin;
		update_user_meta( $this->id, $this->origin_meta_key, $this->origin );
		return $this;
	}

	/**
	 * Get option form elements for a user.
	 * @since 1.0.0
	 * @return string User options HTML.
	 */
	public function profile_options() {
		return html( 'div class="prompt-profile-options"',
			html( 'h2', __( 'Conversation Subscriptions', 'Postmatic' ) ),
			$this->profile_subscribers(),
			$this->profile_site_subscriptions(),
			$this->profile_author_subscriptions(),
			$this->profile_post_subscriptions()
		);
	}

	/**
	 * Save changes made to profile options.
	 * @since 1.0.0
	 * @param array $options
	 */
	public function update_profile_options( $options ) {

		$signup_lists = Prompt_Subscribing::get_signup_lists();
		foreach( $signup_lists as $list ) {
			$this->subscribe_to_checked_lists( get_class( $list ), $options );
		}

		$this->subscribe_to_checked_lists( 'Prompt_Site_Comments', $options );

		foreach ( array( 'Prompt_User', 'Prompt_Post' ) as $unsubscribe_class ) {
			$this->unsubscribe_from_unchecked_lists( $unsubscribe_class, $options );
		}
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function subscription_url() {
		return get_author_posts_url( $this->id );
	}

	/**
	 * @since 2.0.0 Added format parameter
	 * @since 1.0.0
	 *
	 * @param string $format
	 * @return string
	 */
	public function subscription_object_label( $format = Prompt_Enum_Content_Types::HTML ) {
		if ( $format == Prompt_Enum_Content_Types::TEXT ) {
			return Prompt_Content_Handling::reduce_html_to_utf8( $this->subscription_object_label( Prompt_Enum_Content_Types::HTML ) );
		}
		/* translators: %s is the author name */
		return sprintf(
			__( 'posts by %s', 'Postmatic' ),
			$this->get_wp_user()->display_name
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
		if ( $format == Prompt_Enum_Content_Types::TEXT ) {
			return Prompt_Content_Handling::reduce_html_to_utf8( $this->subscription_description( Prompt_Enum_Content_Types::HTML ) );
		}
		/* translators: %s is the author name */
		return sprintf(
			__( 'You have successfully subscribed and will receive posts by %s directly in your inbox.', 'Postmatic' ),
			$this->get_wp_user()->display_name
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
		if ( $format == Prompt_Enum_Content_Types::TEXT ) {
			return Prompt_Content_Handling::reduce_html_to_utf8( $this->select_reply_prompt( Prompt_Enum_Content_Types::HTML ) );
		}

		$subscribe_mailto = sprintf(
			'mailto:{{{reply_to}}}?subject=%s&body=%s',
			__( 'Just hit send', 'Postmatic' ),
			$this->subscribe_phrase()
		);
		return sprintf(
			__(
				'To get new posts by %s as soon as they are published, reply with the phrase \'%s\'.',
				'Postmatic'
			),
			$this->get_wp_user()->display_name,
			"<a href=\"$subscribe_mailto\">{$this->subscribe_phrase()}</a>"
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function subscribe_phrase() {
		return __( 'author', 'Postmatic' ) . ' ' . $this->get_wp_user()->user_login;
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
	 * @since 2.0.0 return unsubscribed lists
	 * @return Prompt_Interface_Subscribable[]
	 */
	public function delete_all_subscriptions() {
		$subscribables = Prompt_Subscribing::get_subscribable_classes();

		$objects = array();
		foreach ( $subscribables as $subscribable ) {
			$object_ids = call_user_func( array( $subscribable, 'subscribed_object_ids' ), $this->id );
			foreach ( $object_ids as $object_id ) {
				$object = new $subscribable( $object_id );
				$object->unsubscribe( $this->id );
				$objects[] = $object;
			}
		}
		return $objects;
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function profile_subscribers() {
		$subscriber_ids = $this->subscriber_ids();

		if ( empty( $subscriber_ids ) )
			return '';

		$subscriber_items = '';
		foreach( $subscriber_ids as $user_id ) {
			$user = get_userdata( $user_id );
			$subscriber_items .= html( 'li', $user->display_name );
		}

		return html(
			'div class="prompt-author-subscriptions"',
			html( 'h4', __( 'People that subscribe to you:', 'Postmatic' ) ),
			html( 'ul', $subscriber_items )
		);
	}

	/**
	 * @since 1.0.0
	 *
	 * @param Prompt_Interface_Subscribable $list
	 * @return string
	 */
	protected function profile_subscription_checkbox( Prompt_Interface_Subscribable $list ) {
		$name = strtolower( get_class( $list ) ) . '_subscribed[]';
		return scbForms::input(
			array(
				'name' => $name,
				'type' => 'checkbox',
				'value' => $list->id(),
				'desc' => $list->subscribe_prompt(),
				'checked' => $list->is_subscribed( $this->id ),
				'extra' => array( 'disabled' => !$list->is_subscribed( $this->id ) and !$this->is_current_user() )
			)
		);
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function profile_site_subscriptions() {

		$signup_lists = Prompt_Subscribing::get_signup_lists();

		$checkboxes = array_map( array( $this, 'profile_subscription_checkbox' ), $signup_lists );

		if ( $this->get_wp_user()->has_cap( 'manage_options' ) ) {
			$site_comments = new Prompt_Site_Comments();
			$checkboxes[] = $this->profile_subscription_checkbox( $site_comments );
		}

		return html( 'div id="prompt-site-subscription"',
			html( 'h4', __( 'Site Subscriptions:', 'Postmatic' ) ),
			implode( '<br/>', $checkboxes )
		);
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function profile_author_subscriptions() {
		$subscribed_author_ids = self::subscribed_object_ids( $this->id );

		if ( empty( $subscribed_author_ids ) )
			return '';

		$author_items = '';
		foreach( $subscribed_author_ids as $author_id ) {
			$author_items .= html( 'li', $this->profile_subscription_checkbox( new Prompt_User( $author_id ) ) );
		}

		return html(
			'div id="prompt-author-subscriptions"',
			html( 'h4', __( 'Authors you subscribe to:', 'Postmatic' ) ),
			html( 'ul', $author_items )
		);
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function profile_post_subscriptions() {
		$subscribed_post_ids = Prompt_Post::subscribed_object_ids( $this->id );

		if ( empty( $subscribed_post_ids ) ) {
			return '';
		}

		$post_items = '';
		foreach ( $subscribed_post_ids as $post_id ) {
			$post_items .= html(
				'li',
				$this->profile_subscription_checkbox( new Prompt_Post( $post_id ) ),
				/* translators: indicates that comments are closed on a post */
				comments_open( $post_id ) ? '' : __( '(closed)', 'Postmatic' )
			);
		}

		return html(
			'div id="prompt-post-subscriptions"',
			html( 'h4', __( 'Discussions you are subscribed to:', 'Postmatic' ) ),

			html( 'ul', $post_items )
		);
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $class
	 * @param array $options
	 */
	protected function subscribe_to_checked_lists( $class, array $options ) {
		$subscribed_ids = call_user_func( array( $class, 'subscribed_object_ids' ), $this->id );
		$name = strtolower( $class ) . '_subscribed';
		$checked_ids = isset( $options[$name] ) ? $options[$name] : array();

		$subscribe_ids = array_diff( $checked_ids, $subscribed_ids );
		if ( $this->is_current_user() ) {
			$this->subscribe_to_ids( $class, $subscribe_ids );
		}

		$unsubscribe_ids = array_diff( $subscribed_ids, $checked_ids );
		$this->unsubscribe_from_ids( $class, $unsubscribe_ids );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $class
	 * @param array $options
	 */
	protected function unsubscribe_from_unchecked_lists( $class, array $options ) {
		$subscribed_ids = call_user_func( array( $class, 'subscribed_object_ids' ), $this->id );
		$name = strtolower( $class ) . '_subscribed';
		$retain_ids = isset( $options[$name] ) ? $options[$name] : array();
		if ( ! empty( $subscribed_ids ) ) {
			$unsubscribe_ids = array_diff( $subscribed_ids, $retain_ids );
			$this->unsubscribe_from_ids( $class, $unsubscribe_ids );
		}
	}

	/**
	 *
	 * @since 2.0.0
	 * @param string $class
	 * @param array $ids
	 */
	protected function subscribe_to_ids( $class, array $ids ) {
		
		if ( !$this->get_subscriber_origin() ) {
			$this->add_profile_subscriber_origin();
		}

		foreach ( $ids as $id ) {
			$list = new $class( $id );
			$list->subscribe( $this->id );
		}
	}

	/**
	 * @since 2.0.0
	 */
	protected function add_profile_subscriber_origin() {
		
		$origin = new Prompt_Subscriber_Origin( array(
			'source_label' => __( 'Existing User', 'Postmatic' ),
			'source_url' => scbUtil::get_current_url(),
			'agreement' => 'checkbox',
		) );
		
		$this->set_subscriber_origin( $origin );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $class
	 * @param array $ids
	 */
	protected function unsubscribe_from_ids( $class, array $ids ) {
		foreach ( $ids as $id ) {
			$list = new $class( $id );
			$list->unsubscribe( $this->id );
		}
	}

	/**
	 * Determine whether this user is currently logged in.
	 * @since 1.0.0
	 * @return bool
	 */
	protected function is_current_user() {
		return $this->id == get_current_user_id();
	}

	/**
	 * Get all the author IDs a user is subscribed to.
	 *
	 * @since 1.0.0
	 * @param $user_id
	 * @return mixed|void
	 */
	public static function subscribed_object_ids( $user_id ) {

		// Using a "fake" object for PHP 5.2, which doesn't have static method inheritance
		$user = new Prompt_User( 0 );

		return $user->_subscribed_object_ids( $user_id );
	}

	/**
	 * Get the IDs of all users subscribed to an author.
	 * @since 1.0.0
	 * @return array
	 */
	public static function all_subscriber_ids() {

		// Using a "fake" object for PHP 5.2, which doesn't have static method inheritance
		$user = new Prompt_User( 0 );

		return $user->_all_subscriber_ids();
	}

}