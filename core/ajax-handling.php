<?php

/**
 * Handle Ajax Requests
 */
class Prompt_Ajax_Handling {
	const AJAX_NONCE = 'prompt_subscribe';

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

}