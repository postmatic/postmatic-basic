<?php

class Prompt_Subscription_Agreement_Wp_Mailer extends Prompt_Wp_Mailer {

	/** @var string */
	protected static $delivery_option = 'prompt_agreement_delivery';

	/** @var  Prompt_Subscription_Agreement_Email_Batch */
	protected $batch;
	
	/** @var  string */
	protected $batch_key;

	/** @var  int */
	protected $chunk;

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Subscription_Agreement_Email_Batch $batch
	 * @param Prompt_Interface_Http_Client|null $client
	 * @param callable|null $local_mailer
	 * @param int $chunk
	 */
	public function __construct(
		Prompt_Subscription_Agreement_Email_Batch $batch,
		Prompt_Interface_Http_Client $client = null,
		callable $local_mailer = null,
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
		$this->set_delivered_chunk();

		$chunk_size = Prompt_Core::$options->get( 'emails_per_chunk' );
		$chunks = array_chunk( $this->batch->get_individual_message_values(), $chunk_size );

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
			$this->schedule_next_chunk();
		}

		return $result;
	}

	/**
	 * Send agreements in a new process.
	 */
	public function schedule() {

		// set to less than first chunk, 0, to allow duplicate mailings
		$this->chunk = -1;;
		$this->set_delivered_chunk();
		$this->schedule_next_chunk();

	}

	/**
	 * @since 2.0.0
	 * @since 2.0.4 Removed batch key argument
	 */
	protected function schedule_next_chunk() {

		$option_key = 'prompt_ac_' . $this->get_batch_key();

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
	 * @since 2.0.4 Removed return value
	 */
	protected function set_delivered_chunk() {

		$delivery = get_option( self::$delivery_option, array() );

		$key = $this->get_batch_key();

		$delivery[$key] = $this->chunk;

		update_option( self::$delivery_option, $delivery, $autoload = false );

		return $key;
	}

	/**
	 * @since 2.0.0
	 * @return int
	 */
	protected function get_delivered_chunk() {

		$delivery = get_option( self::$delivery_option, array() );

		$key = $this->get_batch_key();

		return empty( $delivery[$key] ) ? -1 : $delivery[$key];
	}

	/**
	 * @since 2.0.4
	 * @return string
	 */
	protected function get_batch_key() {
		if ( isset( $this->batch_key ) ) {
			return $this->batch_key;
		}
		
		$key_data = array(
			array_map( array( 'Prompt_Subscribing', 'get_subscribable_slug' ), $this->batch->get_lists() ),
			$this->batch->get_users_data(),
			$this->batch->get_message_data()
		);
		
		$this->batch_key = md5( serialize( $key_data ) );
		
		return $this->batch_key;
	}
}
