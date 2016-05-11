<?php

class Prompt_Subscriber_Origin {

	/** @var  int */
	protected $timestamp;
	/** @var  string */
	protected $source_label;
	/** @var  string */
	protected $source_url;
	/** @var  string */
	protected $agreement;

	public function __construct( $values = array() ) {

		$defaults = array(
			'timestamp' => time(),
			'source_label' => __( 'Unknown source.', 'Postmatic' ),
			'source_url' => home_url(),
			'agreement' => '',
		);

		$values = wp_parse_args( $values, $defaults );

		if ( !is_int( $values['timestamp'] ) )
			$values['timestamp'] = strtotime( $values['timestamp'] );

		foreach ( $values as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}

	}

	/**
	 * @return string
	 */
	public function get_agreement() {
		return $this->agreement;
	}

	/**
	 * @return string
	 */
	public function get_source_label() {
		return $this->source_label;
	}

	/**
	 * @return string
	 */
	public function get_source_url() {
		return $this->source_url;
	}

	/**
	 * @return int
	 */
	public function get_timestamp() {
		return $this->timestamp;
	}

	/**
	 * @param string $format Optional date format.
	 * @return string
	 */
	public function get_date( $format = 'c' ) {
		return date( $format, $this->timestamp );
	}

}