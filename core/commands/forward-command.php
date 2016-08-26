<?php

/**
 * A command that forwards a message
 *
 * @since 2.0.0
 *
 */
class Prompt_Forward_Command implements Prompt_Interface_Command {

	/**
	 * @var string
	 */
	protected static $ignore_method = 'ignore';

	/**
	 * @var string
	 */
	protected static $unsubscribe_method = 'unsubscribe';

	/** @var array */
	protected $keys = array( 'Prompt_Site', 0, 0, 0 );
	/** @var  string */
	protected $subscribable_class;
	/** @var  int */
	protected $subscribable_id;
	/** @var  int */
	protected $from_user_id;
	/** @var  int */
	protected $to_user_id;
	/** @var  object */
	protected $message;
	/** @var  string */
	protected $message_text;

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

		$text_command = $this->get_text_command();
		if ( $text_command ) {
			$this->$text_command( $notify = true );
			return;
		}

		$this->forward();
	}

	/**
	 * @since 2.0.0
	 * @param Prompt_Interface_Subscribable $object
	 * @return $this
	 */
	public function set_subscription_object( Prompt_Interface_Subscribable $object ) {
		$this->subscribable_class = get_class( $object );
		$this->keys[0] = $this->subscribable_class;
		$this->subscribable_id = $object->id();
		$this->keys[1] = $this->subscribable_id;
		return $this;
	}

	/**
	 * @since 2.0.0
	 * @param int $id
	 * @return $this
	 */
	public function set_from_user_id( $id ) {
		$this->from_user_id = intval( $id );
		$this->keys[2] = $this->from_user_id;
		return $this;
	}

	/**
	 * @since 2.0.0
	 * @param int $id
	 * @return $this
	 */
	public function set_to_user_id( $id ) {
		$this->to_user_id = intval( $id );
		$this->keys[3] = $this->to_user_id;
		return $this;
	}

	/**
	 * @since 2.0.0
	 * @return bool
	 */
	protected function validate() {

		if ( !is_array( $this->keys ) or count( $this->keys ) < 4 ) {
			trigger_error( 'Invalid forward keys', E_USER_WARNING );
			return false;
		}

		if ( !empty( $this->keys[0] ) and !class_exists( $this->keys[0] ) ) {
			trigger_error( 'Invalid subscribable class', E_USER_WARNING );
			return false;
		}

		if ( empty( $this->message ) ) {
			trigger_error( 'Invalid message', E_USER_WARNING );
			return false;
		}

		$this->subscribable_class = $this->keys[0];
		$this->subscribable_id = $this->keys[1];
		$this->from_user_id = $this->keys[2];
		$this->to_user_id = $this->keys[3];

		return true;
	}

	/**
	 * @since 2.0.0
	 * @return mixed
	 */
	protected function get_message_text() {
		return $this->message->message;
	}

	/**
	 * Get text command from the message, if any.
	 *
	 * A blank message is treated as a subscribe command.
	 *
	 * @return string Text command if found, otherwise empty.
	 */
	protected function get_text_command() {

		$stripped_text = $this->get_message_text();

		if ( preg_match( '/^\s*$/', $stripped_text, $matches ) ) {
			return self::$ignore_method;
		}

		$unsubscribe_matcher = new Prompt_Unsubscribe_Matcher( $stripped_text );
		if ( $unsubscribe_matcher->matches() ) {
			return self::$unsubscribe_method;
		}

		return '';
	}

	/**
	 * @since 2.0.0
	 * @param bool|false $notify
	 */
	protected function unsubscribe( $notify = true ) {

		if ( empty( $this->subscribable_class ) ) {
			return;
		}

		/** @var Prompt_Interface_Subscribable $list */
		$list = new $this->subscribable_class( $this->subscribable_id );

		if ( !$list->is_subscribed( $this->from_user_id ) ) {
			return;
		}

		$list->unsubscribe( $this->from_user_id );

		if ( $notify ) {
			Prompt_Subscription_Mailing::send_unsubscription_notification( $this->from_user_id, $list );
		}
	}

	/**
	 * @since 2.0.0
	 */
	protected function forward() {

		$text = $this->get_message_text();

		$from_user = get_user_by( 'id', $this->from_user_id );
		$to_user = get_user_by( 'id', $this->to_user_id );

		$command = new Prompt_Forward_Command();
		$command->set_keys( array( '', '', $this->to_user_id, $this->from_user_id ) );

		$template_data = array(
			'sender' => $from_user,
			'message' => $text,
		);

		$html_template = new Prompt_Template( 'forward-email.php' );

		$batch = Prompt_Email_Batch::make_for_single_recipient( array(
			'to_address' => $to_user->user_email,
			'from_name' => $from_user->display_name,
			'subject' => $this->message->subject,
			'html_content' => $html_template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
			'reply_to' => array( 'trackable-address' => Prompt_Command_Handling::get_command_metadata( $command ) ),
		) );

		Prompt_Factory::make_mailer( $batch )->send();
	}

	/**
	 * Ignore an email response.
	 * @since 2.0.0
	 */
	protected function ignore() {}
}