<?php

/**
 * Get a media library image or default alternative.
 */
class Prompt_Attachment_Image {

	/** @var  int */
	protected $attachment_id;
	/** @var  array */
	protected $src;

	/**
	 * @since 1.4.0
	 *
	 * @param int $attachment_id
	 * @param string $default_url
	 * @param int $default_width
	 * @param int $default_height
	 */
	public function __construct( $attachment_id, $default_url = '', $default_width = 0, $default_height = 0 ) {
		$this->attachment_id = $attachment_id;

		$this->src = wp_get_attachment_image_src( $this->attachment_id, 'full' );

		if ( ! $this->src )
			$this->src = array( $default_url, $default_width, $default_height );
	}

	/**
	 * @return string the attachment image URL
	 */
	public function url() {
		return $this->src[0];
	}

	public function width() {
		return $this->src[1];
	}

	public function height() {
		return $this->src[2];
	}
}
