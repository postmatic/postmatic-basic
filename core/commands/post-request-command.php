<?php

/**
 * A command that sends a post by request
 *
 * @since 2.0.0
 *
 */
class Prompt_Post_Request_Command implements Prompt_Interface_Command {

	/** @var array */
	protected $keys = array( 0, 0 );
	/** @var  int */
	protected $post_id;
	/** @var  int */
	protected $user_id;
	/** @var  object */
	protected $message;

	/**
	 * @since 2.0.0
	 * @param $keys
	 */
	public function set_keys( $keys ) {
		$this->keys = $keys;
	}

	/**
	 * @since 2.0.0
	 * @return array
	 */
	public function get_keys() {
		return $this->keys;
	}

	/**
	 * @since 2.0.0
	 * @param $message
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * @since 2.0.0
	 * @return object
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * @since 2.0.0
	 */
	public function execute() {

		if ( !$this->validate() )
			return;

		$this->send_post();
	}

	/**
	 * @since 2.0.0
	 * @param $id
	 */
	public function set_post_id( $id ) {
		$this->post_id = intval( $id );
		$this->keys[0] = $this->post_id;
	}

	/**
	 * @since 2.0.0
	 * @param $id
	 */
	public function set_user_id( $id ) {
		$this->user_id = intval( $id );
		$this->keys[1] = $this->user_id;
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	protected function validate() {

		if ( !is_array( $this->keys ) or count( $this->keys ) < 2 ) {
			trigger_error( __( 'Invalid post request keys', 'Postmatic' ), E_USER_WARNING );
			return false;
		}

		$this->post_id = $this->keys[0];
		$this->user_id = $this->keys[1];

		return true;
	}

	/**
	 * @since 2.0.0
	 */
	protected function send_post() {

		$post = get_post( $this->post_id );

		$context = new Prompt_Post_Rendering_Context( $post );

		$context->setup();

		$batch = new Prompt_Post_Email_Batch( $context, array( 'excerpt_only' => false ) );

		$batch->add_recipient( new Prompt_User( $this->user_id ) );

		$context->reset();

		Prompt_Factory::make_post_adhoc_mailer( $batch )->send();

	}

}