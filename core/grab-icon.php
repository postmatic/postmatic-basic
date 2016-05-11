<?php

class Prompt_Grab_Icon {

	/** @var string */
	protected static $api_key = '1819c1c696999cb5';

	/** @var  int */
	protected $size;
	/** @var  int */
	protected $attachment_id;

	public function __construct( $size = 64 ) {
		$this->size = $size;
	}

	/**
	 * @return string
	 */
	public function get_attachment_id() {
		if ( ! $this->attachment_id )
			$this->sideload_icon();
		return $this->attachment_id;
	}

	/**
	 * Get a fresh icon image from grabicon.com and cache it.
	 */
	public function sideload_icon() {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$grab_base_url = 'http://grabicon.com/icon';

		$home_url_parts = parse_url( home_url() );

		$grab_url = add_query_arg(
			array(
				'size' => $this->size,
				'domain' => $home_url_parts['host'],
				'origin' => $home_url_parts['host'],
				'reset' => 'true',
				'key' => self::$api_key,
			),
			$grab_base_url
		);

		$file_info = array(
			'name' => 'prompt-site-icon-' . $this->size . '.png',
			'tmp_name' => download_url( $grab_url, 5 ),
		);

		if ( is_wp_error( $file_info['tmp_name'] ) )
			return;

		$id = media_handle_sideload( $file_info, 0 );

		if ( !is_wp_error( $id ) )
			$this->attachment_id = $id;

	}

}