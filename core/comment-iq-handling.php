<?php

class Prompt_Comment_IQ_Handling {

	public static function maybe_sync_post_article( $post_id, WP_Post $post ) {

		if ( 'publish' != $post->post_status ) {
			return;
		}

		if ( ! Prompt_Core::$options->is_api_transport() ) {
			return;
		}

		if ( ! in_array( $post->post_type, Prompt_Core::$options->get( 'site_subscription_post_types' ) ) ) {
			return;
		}

	}

}