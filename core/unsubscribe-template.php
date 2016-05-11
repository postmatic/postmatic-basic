<?php

class Prompt_Unsubscribe_Template extends Prompt_Template {

	protected $subscriber;

	public function __construct() {
		parent::__construct( 'unsubscribe-view.php' );
	}

	public function render( $args, $echo = true ) {

		$status = $this->execute( $args );

		$data = array(
			'site' => new Prompt_Site(),
			'subscriber' => $this->subscriber,
			'status' => $status,
			'suppress_delivery' => true,
			'footer_type' => Prompt_Enum_Email_Footer_Types::TEXT,
		);

		$wrapper_template = new Prompt_Template( 'html-local-email-wrapper.php' );

		$wrapper_template->render( array( 'html_content' => parent::render( $data, false ), ), true );
	}

	protected function execute( $args ) {

		$link = new Prompt_Unsubscribe_Link( $args );

		if ( !$link->is_valid() )
			return __(
				'We tried to unsubscribe you, but there was some required information missing from this request.',
				'Postmatic'
			);

		$this->subscriber = $link->user();

		$prompt_user = new Prompt_User( $this->subscriber );

		$prompt_user->delete_all_subscriptions();

		return sprintf(
			__( 'Got it. %s has been unsubscribed from new posts as well as any conversations.', 'Postmatic' ), $this->subscriber->user_email
		);
	}
}