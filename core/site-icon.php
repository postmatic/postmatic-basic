<?php

/**
 * Manage the site icon used in Postmatic emails
 *
 * Prefer the native site icon, falling back to grabicon when missing.
 *
 * @since 2.0.0
 *
 */
class Prompt_Site_Icon {

	/**
	 * The current site icon URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function url() {

		$url = get_site_icon_url( 64 );

		if ( $url ) {
			return $url;
		}

		$attachment_image = new Prompt_Attachment_Image( Prompt_Core::$options->get( 'site_icon' ) );

		$url = $attachment_image->url();

		if ( $url ) {
			return $url;
		}

		return path_join( Prompt_Core::$url_path, 'media/prompt-site-icon-64.png' );
	}

	/**
	 * Ensure we're using the most current site icon.
	 *
	 * Does nothing if native icon is in use, but will create a new grabicon.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function refresh() {

		$current_attachment_id = Prompt_Core::$options->get( 'site_icon' );
		if ( $current_attachment_id ) {
			wp_delete_attachment( $current_attachment_id, $full_delete = true );
		}

		if ( get_site_icon_url( 64 ) ) {
			return;
		}

		$icon = new Prompt_Grab_Icon();

		// If the request failed, set to -1 to prevent retries
		$attachment_id = $icon->get_attachment_id() ? $icon->get_attachment_id() : -1;

		Prompt_Core::$options->set( 'site_icon', $attachment_id );
	}

	/**
	 * If there is no current site icon create one.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function ensure() {

		if ( get_site_icon_url( 64 ) ) {
			return;
		}

		if ( Prompt_Core::$options->get( 'site_icon' ) ) {
			return;
		}

		self::refresh();
	}
}