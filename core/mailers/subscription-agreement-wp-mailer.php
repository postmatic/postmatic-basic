<?php

/**
 * Manage sending subscription agreements locally.
 * @since 2.0.0
 */
class Prompt_Subscription_Agreement_Wp_Mailer extends Prompt_Wp_Mailer {

	/** @var  Prompt_Subscription_Agreement_Email_Batch */
	protected $batch;
	
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
	 * Used to spawn a process to send, now just sends directly.
	 */
	public function schedule() {
		$this->send();
	}

}
