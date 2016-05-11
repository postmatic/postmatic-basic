<?php

class Prompt_Subscription_Agreement_Wp_Mailer extends Prompt_Wp_Mailer {

	/** @var string */
	protected static $delivery_option = 'prompt_agreement_delivery';

	/** @var  Prompt_Subscription_Agreement_Email_Batch */
	protected $batch;

	/** @var  int */
	protected $chunk;

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Subscription_Agreement_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client|null $client
	 * @param PHPMailer|null $local_mailer
	 * @param int $chunk
	 */
	public function __construct(
		Prompt_Subscription_Agreement_Email_Batch $batch,
		Prompt_Interface_Http_Client $client = null,
		PHPMailer $local_mailer = null,
		$chunk = 0
	) {
		parent::__construct( $batch, $client, $local_mailer, $chunk );
	}

	/**
	 * Augment sending to add chunking.
	 *
	 * @return array
	 */
	public function send() {

		// Bail if we've already sent this chunk
		if ( $this->chunk <= $this->get_delivered_chunk() ) {
			return array();
		}

		// Block other processes from sending this chunk
		$batch_key = $this->set_delivered_chunk();

		$chunks = array_chunk( $this->batch->get_individual_message_values(), 30 );

		$this->batch->set_individual_message_values( $chunks[$this->chunk] );

		$result = parent::send();

		if ( is_wp_error( $result ) ) {

			Prompt_Logging::add_error(
				Prompt_Enum_Error_Codes::OUTBOUND,
				__( 'A subscription agreement sending operation encountered a problem.', 'Postmatic' ),
				array( 'error' => $result, 'batch' => $this->batch, 'chunk' => $this->chunk )
			);

		}

		if ( !empty( $chunks[$this->chunk + 1] ) ) {
			$this->schedule_next_chunk( $batch_key );
		}

		return $result;
	}

	/**
	 * Send agreements in a new process.
	 */
	public function schedule() {

		// set to less than first chunk, 0, to allow duplicate mailings
		$this->chunk = -1;;

		$this->schedule_next_chunk( $this->set_delivered_chunk() );

	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $batch_key
	 */
	protected function schedule_next_chunk( $batch_key ) {

		$option_key = 'prompt_ac_' . $batch_key;

		update_option(
			$option_key,
			array(
				$this->batch->get_lists(),
				$this->batch->get_users_data(),
				$this->batch->get_message_data(),
				$this->chunk + 1,
			),
			false
		);

		$this->client->post_instant_callback(
			array(
				'metadata' => array(
					'prompt/subscription_mailing/send_cached_invites',
					array( $option_key ),
				),
			)
		);

	}

	/**
	 * @since 2.0.0
	 * @return string batch key
	 */
	protected function set_delivered_chunk() {

		$delivery = get_option( self::$delivery_option, array() );

		$key = md5( serialize( $this->batch ) );

		$delivery[$key] = $this->chunk;

		update_option( self::$delivery_option, $delivery, $autoload = false );

		return $key;
	}

	/**
	 * @since 2.0.0
	 *
	 * @return int
	 */
	protected function get_delivered_chunk() {

		$delivery = get_option( self::$delivery_option, array() );

		$key = md5( serialize( $this->batch ) );

		return empty( $delivery[$key] ) ? -1 : $delivery[$key];
	}

}
