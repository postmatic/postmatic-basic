<?php

class Prompt_Comment_Form_Handling {

	const SUBSCRIBE_CHECKBOX_NAME = 'prompt_comment_subscribe';
	const UNSUBSCRIBE_ACTION = 'prompt_comment_unsubscribe';

	/** @var Prompt_Post */
	protected static $prompt_post;

	/**
	 * Handle comment form submissions.
	 *
	 * Called by the comment_post action.
	 *
	 * @param int $comment_id
	 * @param string $status
	 */
	public static function handle_form( $comment_id, $status ) {

		if ( !Prompt_Core::$options->get( 'enable_comment_delivery' )  or 'spam' === $status )
			return;

		$comment = new Prompt_Comment( $comment_id );

		if ( !$comment->author_can_subscribe() ) {
			return;
		}

		$checked = isset( $_POST[self::SUBSCRIBE_CHECKBOX_NAME] );

		if ( !$checked ) {
			return;
		}

		if ( 0 == $status ) {
			$comment->set_subscription_requested();
			return;
		}

		self::subscribe_commenter( $comment->get_wp_comment() );
	}

	/**
	 * Determine whether a subscription was requested on a moderated comment.
	 *
	 * @param object $comment
	 * @return boolean
	 */
	public static function subscription_requested( $comment ) {
		$prompt_comment = new Prompt_Comment( $comment );
		return $prompt_comment->get_subscription_requested();
	}

	/**
	 * Subscribe a commenter.
	 *
	 * @param object $comment
	 */
	public static function subscribe_commenter( $comment ) {

		$user_id = $comment->user_id;

		if ( !$user_id ) {
			$user = get_user_by( 'email', $comment->comment_author_email );
			$user_id = $user ? $user->ID : null;
		}

		$prompt_post = new Prompt_Post( $comment->comment_post_ID );

		if ( !$user_id ) {

			$user_data = array(
				'display_name' => $comment->comment_author,
				'user_url' => $comment->comment_author_url,
			);

			Prompt_Subscription_Mailing::send_agreement(
				$prompt_post,
				$comment->comment_author_email,
				$user_data
			);

			return;
		}

		if ( !$prompt_post->is_subscribed( $user_id ) ) {

			$prompt_post->subscribe( $user_id );

			Prompt_Subscription_Mailing::send_subscription_notification( $user_id, $prompt_post );
		}
	}

	/**
	 * @since 2.0.0
	 *
	 * @param int $post_id Optionally supply which post to subscribe to.
	 */
	public static function enqueue_assets( $post_id ) {

		wp_enqueue_style(
			'prompt-comment-form',
			path_join( Prompt_Core::$url_path, 'css/comment-form.css' ),
			array(),
			Prompt_Core::version()
		);

		$script = new Prompt_Script( array(
			'handle' => 'prompt-comment-form',
			'path' => 'js/comment-form.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();

		$script->localize(
			'prompt_comment_form_env',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE ),
				'action' => self::UNSUBSCRIBE_ACTION,
				'post_id' => $post_id,
			)
		);

	}

	/**
	 * @since 2.0.0
	 *
	 * @param array $handles
	 * @return array
	 */
	public static function enqueue_epoch_assets( array $handles ) {

		self::enqueue_assets( get_the_ID() );

		$handles[] = 'prompt-comment-form';

		return $handles;
	}

	/**
	 * Echo comment form content.
	 *
	 * Called by the comment_form action.
	 *
	 * @param $post_id
	 */
	public static function form_content( $post_id ) {

		if ( !Prompt_Core::$options->get( 'enable_comment_delivery' ) ) {
			return;
		}

		self::enqueue_assets( $post_id );

		self::$prompt_post = new Prompt_Post( $post_id );

		$current_user = Prompt_User_Handling::current_user();

		if ( $current_user and self::$prompt_post->is_subscribed( $current_user->ID ) ) {
			return;
		}
		
		$tooltip_text = __( 
			'Get only replies to your comment, the best of the rest, as well as a daily recap of all comments on this post. No more than a few emails daily, which you can reply to/unsubscribe from directly from your inbox.', 
			'Postmatic' 
		);
		
		if ( ! Prompt_Core::$options->is_api_transport() ) {
			$tooltip_text = __(
				"Get notified of new comments on this post. If discussion generates more than a few emails daily your subscription will be paused automatically.",
				'Postmatic'
			);
		}

		$tooltip_text = apply_filters( 'replyable/comment_form/opt_in_tooltip_text', $tooltip_text );

		echo html( 'label id="prompt-comment-subscribe"',
			html( 'input',
				array(
					'type' => 'checkbox',
					'name' => self::SUBSCRIBE_CHECKBOX_NAME,
					'value' => '1',
					'checked' => Prompt_Core::$options->get( 'comment_opt_in_default' ),
				)
			),
			'&nbsp;',
			html( 'span class="postmatic-tooltip"',
				Prompt_Core::$options->get( 'comment_opt_in_text' ),
				html( 'em', $tooltip_text )
			)
		);

	}

	public static function after_form() {

		if ( !Prompt_Core::$options->get( 'enable_comment_delivery' ) or empty( self::$prompt_post ) ) {
			return;
		}

		$current_user = Prompt_User_Handling::current_user();

		if ( !$current_user or !self::$prompt_post->is_subscribed( $current_user->ID ) ) {
			return;
		}


		echo html( 'div class="prompt-unsubscribe"',
			html( 'div class=".loading-indicator" style="display: none;"',
				html( 'img', array( 'src' => path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' ) ) )
			),
			html( 'p',
				__( 'You are subscribed to new comments on this post.', 'Postmatic' )
			),
			scbForms::input( array(
				'type' => 'submit',
				'name' => self::UNSUBSCRIBE_ACTION,
				'value' => __( 'Unsubscribe', 'Postmatic' ),
			) )
		);

	}

}