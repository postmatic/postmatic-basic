<?php

/**
 * Handle Ajax Requests
 */
class Prompt_Ajax_Handling {
	const AJAX_NONCE = 'prompt_subscribe';

	/**
	 * Handle subscription ajax requests from the subscribe widget.
	 */
	static public function action_wp_ajax_prompt_subscribe() {

		$validity = self::validate_subscribe_request();
		if ( $validity !== true ) {
			wp_die( $validity );
		}

		$subscriber = wp_get_current_user();

		/** @var Prompt_Interface_Subscribable $target_list */
		$target_list = null;

		if ( ! empty( $_POST['object_id'] ) and ! empty( $_POST['object_type'] ) ) {
			$object_id = intval( $_POST['object_id'] );
			$object_type = sanitize_text_field( $_POST['object_type'] );
			$target_list = new $object_type( $object_id );
		}

		$email = isset( $_POST['subscribe_email'] ) ? sanitize_email( $_POST['subscribe_email'] ) : null;
		$name = isset( $_POST['subscribe_name'] ) ? sanitize_text_field( $_POST['subscribe_name'] ) : null;
		$mode = isset( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : 'subscribe';

		$found_by_email = false;

		if ( !$subscriber->exists() and $email ) {
			self::set_subscriber_cookies( $email, $name );
			$subscriber = get_user_by( 'email', $email );
			$found_by_email = (bool)$subscriber;
		}

		if ( !$found_by_email and $email ) {
			echo self::verify_new_subscriber( $target_list, $email, $name );
			wp_die();
		}

		if ( !$target_list ) {
			echo self::subscribe_to_signup_lists( $subscriber );
			wp_die();
		}

		if ( $target_list->is_subscribed( $subscriber->ID ) and 'subscribe' == $mode ) {
			printf( __( 'You are already subscribed to %s.', 'Postmatic' ), $target_list->subscription_object_label() );
			wp_die();
		}

		if ( $target_list->is_subscribed( $subscriber->ID ) and 'unsubscribe' == $mode ) {
			echo self::unsubscribe( $target_list, $subscriber, $found_by_email );
			wp_die();
		}

		echo self::subscribe( $target_list, $subscriber );
		wp_die();
	}

	/**
	 * Handle unsubscribe requests from the comment form.
	 */
	public static function action_wp_ajax_prompt_comment_unsubscribe() {

		if ( !wp_verify_nonce( $_POST['nonce'], self::AJAX_NONCE ) )
			wp_die( -1 );

		$post_id = absint( $_POST['post_id'] );

		if ( !$post_id )
			wp_die( 0 );

		$current_user = Prompt_User_Handling::current_user();

		$prompt_post = new Prompt_Post( $post_id );

		if ( !$current_user or !$prompt_post->is_subscribed( $current_user->ID ) )
			wp_die( 0 );

		$prompt_post->unsubscribe( $current_user->ID );

		_e( 'You have unsubscribed.', 'Postmatic' );

		wp_die();
	}

	/**
	 * Handle post editor delivery status requests.
	 */
	public static function action_wp_ajax_prompt_post_delivery_status() {

		$post_id = absint( $_GET['post_id'] );

		if ( !$post_id )
			wp_die( 0 );

		wp_send_json( Prompt_Admin_Delivery_Metabox::status( $post_id ) );
	}

	/**
	 * Handle post editor preview email requests.
	 */
	public static function action_wp_ajax_prompt_post_delivery_preview() {
		$post_id = absint( $_GET['post_id'] );

		if ( !$post_id )
			wp_die( 0 );

		$post = get_post( $post_id );

		$context = new Prompt_Post_Rendering_Context( $post );

		$context->setup();

		$batch = new Prompt_Post_Email_Batch( $context );

		$batch->add_recipient( new Prompt_User( wp_get_current_user() ) );

		$context->reset();

		Prompt_Factory::make_post_adhoc_mailer( $batch )->send();

		wp_send_json( array( 'message' => __( 'Preview email sent.', 'Postmatic' ) ) );
	}

	/**
	 * Handle dynamic widget content requests.
	 */
	public static function action_wp_ajax_prompt_subscribe_widget_content() {

		$widget_id = filter_input( INPUT_GET, 'widget_id', FILTER_SANITIZE_URL );

		$instance = array(
			'collect_name' => filter_input( INPUT_GET, 'collect_name', FILTER_VALIDATE_BOOLEAN ),
			'subscribe_prompt' => filter_input( INPUT_GET, 'subscribe_prompt', FILTER_SANITIZE_STRING ),
		);

		if (
			! empty( $_GET['list_type'] )
			and
			'Prompt_' == substr( $_GET['list_type'], 0, 7 )
			and
			! empty( $_GET['list_id'] )
		) {
			$instance['list'] = new $_GET['list_type']( $_GET['list_id'] );
		}

		Prompt_Subscribe_Widget::render_dynamic_content( $widget_id, $instance );

		wp_die();
	}

	/**
	 * Handle mailchimp lists loading
	 * @since 1.2.3
	 */
	public static function action_wp_ajax_prompt_mailchimp_get_lists() {

		if( empty( $_POST['api_key'] ) ){
			wp_send_json_error( array( 'error' => __( 'An API Key is required', 'Postmatic' ) ) );
		}

		if ( !class_exists( 'Mailchimp' ) ) {
			require_once dirname( dirname( __FILE__ ) ) . '/vendor/mailchimp/mailchimp/src/Mailchimp.php';
		}

		$api_key = sanitize_text_field( $_POST['api_key'] );

		$mailchimp = new Mailchimp( $api_key );
		try {
			$mailchimp_lists = $mailchimp->call(
				'lists/list',
				array( 'filters' => array( 'created_before' => date('Y-m-d H:i:s', strtotime( '-14 days' ) ) ) )
			);
		} catch (Exception $e) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}

		if ( empty( $mailchimp_lists['data'] ) ) {
			wp_send_json_error( array(
				'error' => sprintf(
					__(
						'We\'re sorry. None of your lists qualified. <a href="%s">Click here for more information</a>',
						'Postmatic'
					),
					'http://docs.gopostmatic.com/article/144-im-having-trouble-importing-my-mailchimp-lists'
				),
			) );
		}

		$mailchimp_list_options = array();
		foreach ( $mailchimp_lists['data'] as $list ) {
			$mailchimp_list_options[] = html( 'option',
				array( 'value' => $list['id'] ),
				$list['name'],
				' (',
				$list['stats']['member_count'],
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

		$content = html( 'div',
			html( 'label for="import_list"',
				__( 'Choose a Mailchimp list to import from:', 'Postmatic' ),
				' ',
				html( 'select',
					array( 'name' => 'import_list', 'type' => 'select' ),
					implode( '', $mailchimp_list_options )
				)
			),
			'<br/>',
			html( 'label id="signup_list_index_label" for="signup_list_index" style="display: none;"',
				__( 'Choose a Postmatic list to import to:', 'Postmatic' ),
				' ',
				html( 'select',
					array( 'name' => 'signup_list_index', 'type' => 'select' ),
					implode( '', $local_list_options )
				)
			)
		);

		wp_send_json_success( $content );
	}

	/**
	 * Send whether we are connected or not
	 * @since 2.0.0
	 */
	public static function action_wp_ajax_prompt_is_connected() {
		$is_connected = ( Prompt_Core::$options->get( 'connection_status' ) == Prompt_Enum_Connection_Status::CONNECTED );
		wp_send_json_success( $is_connected );
	}

	/**
	 * Dismiss a notice.
	 * @since 2.0.0
	 */
	public static function action_wp_ajax_prompt_dismiss_notice() {

		if ( empty( $_GET['class'] ) or ! class_exists( $_GET['class'] ) ) {
			wp_send_json_error();
		}

		$notice = new $_GET['class'];

		$notice->dismiss();

		wp_send_json_success();
	}

	/**
	 * @param $post_id
	 * @return array|bool
	 */
	protected static function featured_image_src( $post_id ) {

		if ( Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post_id ) )
			return false;

		$featured_image = image_get_intermediate_size( get_post_thumbnail_id( $post_id ), 'prompt-post-featured' );

		if ( ! $featured_image )
			$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );

		if ( ! $featured_image )
			return false;

		return array( $featured_image['url'], $featured_image['width'], $featured_image['height'] );
	}

	/**
	 * @param string $email
	 * @param string $name
	 */
	protected static function set_subscriber_cookies( $email, $name ) {
		$commenter = wp_get_current_commenter();

		$comment = new stdClass();
		$comment->comment_author = $name;
		$comment->comment_author_email = $email;
		$comment->comment_author_url = $commenter['comment_author_url'];

		wp_set_comment_cookies( $comment, wp_get_current_user() );
	}

	/**
	 * @return bool|int|string True if valid, a message if correctable, otherwise -1.
	 */
	protected static function validate_subscribe_request() {

		if ( !wp_verify_nonce( $_POST['subscribe_nonce'], self::AJAX_NONCE ) ) {
			$message = sprintf(
				'Postmatic subscribe bad nonce request %s post data %s.',
				json_encode( $_SERVER ),
				json_encode( $_POST )
			);
			trigger_error( $message, E_USER_NOTICE );
			return -1;
		}

		if ( !isset( $_POST['subscribe_topic'] ) or !empty( $_POST['subscribe_topic'] ) ) {
			$message = sprintf(
				'Postmatic subscribe bad topic request %s post data %s.',
				json_encode( $_SERVER ),
				json_encode( $_POST )
			);
			trigger_error( $message, E_USER_NOTICE );
			return -1;
		}

		if ( isset( $_POST['subscribe_email'] ) and is_email( $_POST['subscribe_email'] ) === false ) {
			return html( 'div class="error"', __( 'Sorry, that email address is not valid.', 'Postmatic' ) );
		}

		return true;
	}

	/**
	 * @param Prompt_Interface_Subscribable $object
	 * @param string $email
	 * @param string $name
	 * @return string
	 */
	protected static function verify_new_subscriber( $object, $email, $name ) {

		$lists = $object ? $object : Prompt_Subscribing::get_signup_lists();

		$display_name = sanitize_text_field( $name );
		$name_words = explode( ' ', trim( $name ) );
		$first_name = array_shift( $name_words );
		$last_name = empty( $name_words ) ? '' : implode( ' ', $name_words );

		$user_data = compact( 'first_name', 'last_name', 'display_name' );

		Prompt_Subscription_Mailing::send_agreement( $lists, $email, $user_data );

		$message = html( 'strong',
			__( 'Almost done - you\'ll receive an email with instructions to complete your subscription.', 'Postmatic' ),
			' '
		)  ;

		/**
		 * Filter the account created Ajax message.
		 *
		 * @param string $message
		 * @param string $email
		 * @param array $user_data
		 */
		return apply_filters( 'prompt/ajax/subscription_verification_message', $message, $email, $user_data );
	}

	/**
	 * @param Prompt_Interface_Subscribable $object
	 * @param WP_User $subscriber
	 * @param boolean $found_by_email
	 * @return string Step response message
	 */
	protected static function unsubscribe( $object, $subscriber, $found_by_email ) {

		$object->unsubscribe( $subscriber->ID );

		Prompt_Subscription_Mailing::send_unsubscription_notification( $subscriber->ID, $object );

		return __( 'You have unsubscribed.', 'Postmatic' );
	}

	/**
	 * @param Prompt_Interface_Subscribable|Prompt_Interface_Subscribable[] $lists
	 * @param WP_User $subscriber
	 * @return string Response
	 */
	protected static function subscribe( $lists, $subscriber ) {

		$lists = is_array( $lists ) ? $lists : array( $lists );

		foreach ( $lists as $list ) {
			$list->subscribe( $subscriber->ID );
			Prompt_Subscription_Mailing::send_subscription_notification( $subscriber->ID, $list );
		}

		return __(
			'<strong>Confirmation email sent. Please check your email for further instructions.</strong>',
			'Postmatic'
		);
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $subscriber
	 * @return string
	 */
	protected static function subscribe_to_signup_lists( $subscriber ) {

		$lists = Prompt_Subscribing::get_signup_lists();

		$subscribed_labels = array();
		foreach ( $lists as $list ) {
			if ( $list->is_subscribed( $subscriber->ID ) ) {
				$subscribed_labels[] = $list->subscription_object_label();
			}
		}

		if ( $subscribed_labels ) {
			return sprintf(
				__( 'You are already subscribed to %s.', 'Postmatic' ),
				implode( ' &amp; ', $subscribed_labels )
			);
		}

		return self::subscribe( $lists, $subscriber );
	}

}