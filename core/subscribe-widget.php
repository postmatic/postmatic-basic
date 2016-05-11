<?php

/**
 * Subscribe widget
 *
 * @since 1.0.0
 */
class Prompt_Subscribe_Widget extends WP_Widget {

	public function __construct() {
		$default_options = array(
			'description' => __( 'Get visitors subscribed with minimal fuss.', 'Postmatic' )
		);
		parent::__construct( false, __( 'Postmatic Subscribe', 'Postmatic' ), $default_options );
	}

	/**
	 * Display widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$instance_defaults = array(
			'title' => '',
			'collect_name' => true,
			'subscribe_prompt' => null,
			'list' => $this->get_context_list(),
		);

		$instance = wp_parse_args( $instance, $instance_defaults );

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];

		$this->enqueue_widget_assets();

		$container_attributes = array(
			'class' => 'prompt-subscribe-widget-content',
			'data-widget-id' => $this->id,
			'data-collect-name' => (int) $instance['collect_name'],
			'data-subscribe-prompt' => $instance['subscribe_prompt'],
			'data-list-type' => $instance['list'] ? get_class( $instance['list'] ) : '',
			'data-list-id' => $instance['list'] ? $instance['list']->id() : '',
		);

		echo html( 'div',$container_attributes );

		echo $args['after_widget'];
	}


	/**
	 * Process updates from form
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['collect_name'] = isset( $new_instance['collect_name'] ) ? true : false;
		$instance['subscribe_prompt'] = sanitize_text_field( $new_instance['subscribe_prompt'] );


		return $instance;
	}


	/**
	 * Show the form
	 *
	 * @param array $instance
	 * @return string API oddness?
	 */
	public function form( $instance ) {
		$template = new Prompt_Template( 'subscribe-widget-settings.php' );
		$template_data = array( 'widget' => $this, 'instance' => $instance );
		$template->render( $template_data, $echo = true );
		return '';
	}

	/**
	 * Get an escaped instance value or a fallback if not set
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance
	 * @param string $field
	 * @param string $fallback
	 * @param string $escape_callback
	 * @return string
	 */
	public function get_default_value( $instance, $field, $fallback = '', $escape_callback = 'esc_attr' ) {
		if ( isset( $instance[$field] ) )
			$value = $instance[$field];
		else
			$value = $fallback;

		if ( function_exists( $escape_callback ) )
			$value = call_user_func( $escape_callback, $value );

		return $value;
	}

	public static function subscribe_action() {
		return Prompt_Subscribe_Matcher::target();
	}

	public static function unsubscribe_action() {
		return Prompt_Unsubscribe_Matcher::target();
	}

	/**
	 * Emit markup for the dynamic portion of the widget content.
	 *
	 * @since 2.0.0 Include an optional target list in instance
	 * @since 1.0.0
	 *
	 * @param string $widget_id
	 * @param array $instance {
	 *      Widget options
	 * @type boolean $collect_name
	 * @type string $subscribe_prompt
	 * @type Prompt_Interface_Subscribable $list
	 * }
	 */
	public static function render_dynamic_content( $widget_id, $instance ) {

		$commenter = wp_get_current_commenter();
		$defaults = array(
			'subscribe_name' => $commenter['comment_author'] ? $commenter['comment_author'] : '',
			'subscribe_email' => $commenter['comment_author_email'] ? $commenter['comment_author_email'] : '',
		);

		$user = is_user_logged_in() ? wp_get_current_user() : null;

		$object = empty( $instance['list'] ) ? null : $instance['list'];

		if ( $user and $object and $object->is_subscribed( $user->ID ) ) {
			$mode = 'unsubscribe';
			$action = self::unsubscribe_action();
		} else {
			$mode = 'subscribe';
			$action = self::subscribe_action();
		}

		$loading_image_url = path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' );

		$template_data = compact(
			'widget_id',
			'instance',
			'object',
			'mode',
			'action',
			'defaults',
			'loading_image_url'
		);

		$template = new Prompt_Template( 'subscribe-form.php' );

		$template->render( $template_data, $echo = true );
	}

	protected function enqueue_widget_assets() {

		if ( wp_script_is( 'prompt-subscribe-form' ) ) {
			return;
		}

		wp_enqueue_style(
			'prompt-subscribe-form',
			path_join( Prompt_Core::$url_path, 'css/subscribe-form.css' ),
			array(),
			Prompt_Core::version()
		);

		$script = new Prompt_Script( array(
			'handle' => 'prompt-subscribe-form',
			'path' => 'js/subscribe-form.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();

		$localize_data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'spinner_url' => path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' ),
			'nonce' => wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE ),
			'subscribe_action' => self::subscribe_action(),
			'unsubscribe_action' => self::unsubscribe_action(),
			'ajax_error_message' => __( 'Sorry, there was a problem reaching the server', 'Postmatic' ),
			'object_type' => null,
			'object_id' => null,
		);

		$script->localize( 'prompt_subscribe_form_env', $localize_data );

	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return null|Prompt_Interface_Subscribable
	 */
	protected function get_context_list() {

		$default_object = get_queried_object();

		// The widget will offer site subscriptions on single posts
		$object = is_a( $default_object, 'WP_Post' ) ? null : $default_object;

		/**
		 * Filter the target object for the subscription widget.
		 *
		 * @param object $object The post, user, etc.
		 * @param Prompt_Subscribe_Widget $widget
		 * @param array $instance The widget instance data.
		 */
		$object = apply_filters( 'prompt/subscribe_widget_object', $object, $this );

		if ( ! $object ) {
			// Just use a default list
			return null;
		}

		return Prompt_Subscribing::make_subscribable( $object );
	}

	protected static function subscribe_prompt( $instance, Prompt_Interface_Subscribable $object ) {

		if ( !empty( $instance['subscribe_prompt'] ) )
			return esc_html( $instance['subscribe_prompt'] );

		if ( is_user_logged_in() )
			return sprintf( __( 'Subscribe to %s:', 'Postmatic' ), $object->subscription_object_label() );

		return sprintf(
			__( 'Enter your email to subscribe to %s:', 'Postmatic' ), $object->subscription_object_label()
		);
	}

 }
