<?php

/**
 * Mailchimp Import
 * @since 1.2.3
 */
class Prompt_Admin_MailChimp_Import {

	/** @var  string */
	protected $api_key;
	/** @var  array */
	protected $list_id;
	/** @var  array */
	protected $subscribers;
	/** @var int */
	protected $already_subscribed_count = 0;
	/** @var int */
	protected $imported_count = 0;
	/** @var  array */
	protected $rejects;
	/** @var  Prompt_Interface_Subscribable */
	protected $target_list;

	public function __construct( $api_key, $list_id, Prompt_Interface_Subscribable $target_list = null ) {
		$this->api_key = $api_key;
		$this->list_id = $list_id;
		$this->rejects = array();
		$this->target_list = $target_list ? $target_list : new Prompt_Site();
	}

	public function get_error() {
		return null;
	}

	public function get_subscriber_count() {
		$this->ensure_subscribers();
		return count( $this->subscribers );
	}

	public function get_imported_count() {
		return $this->imported_count;
	}

	public function get_already_subscribed_count() {
		return $this->already_subscribed_count;
	}

	public function get_rejected_subscribers() {
		return $this->rejects;
	}

	public function execute() {
		$this->ensure_subscribers();

		foreach ( $this->subscribers as $subscriber ) {
			$this->import( $subscriber );
		}
	}

	protected function ensure_subscribers() {
		if ( isset( $this->subscribers ) )
			return;

		$this->subscribers = array();

		// pull in the lib
		if ( !class_exists( 'Mailchimp' ) )
			require_once dirname( dirname( __FILE__ ) ) . '/vendor/mailchimp/mailchimp/src/Mailchimp.php';

		// load subscribers from list
		$mailchimp = new Mailchimp( $this->api_key );
		$start = 0;
		$run = true;
		while( true === $run ){
			$list_subscribers = $mailchimp->call( 'lists/members', array(
				'id'	=>	$this->list_id,
				'opts'	=> array(
					'start' => $start++,
					'limit' => 100
				)
			) );	
			foreach ( $list_subscribers['data'] as $list_subscriber ) {
				$this->add_source_subscriber( $list_subscriber );
			}
			// increment start
			if( $list_subscribers['total'] < 100 || ceil( $list_subscribers['total'] / 100 ) < $start ){
				// continue!
				$run = false;
			}
			// kill off infinate loops
		}
	}

	protected function add_source_subscriber( $subscriber ) {

		if ( $this->is_valid_subscriber( $subscriber ) )
			$this->subscribers[] = $subscriber;
		else
			$this->rejects[] = $subscriber;

	}

	protected function is_valid_subscriber( $subscriber ) {
		if ( empty( $subscriber['timestamp_signup'] ) && empty( $subscriber['ip_signup'] ) )
			return false;

		return true;
	}

	/**
	 * @since 1.2.3
	 * @since 2.0.0 Removed list parameter
	 *
	 * @param array $subscriber
	 */
	protected function import( $subscriber ) {

		$existing_user = get_user_by( 'email', $subscriber['email'] );

		if ( $existing_user and $this->target_list->is_subscribed( $existing_user->ID ) ) {
			$this->already_subscribed_count++;
			return;
		}

		if ( !$existing_user ) {
			$subscriber_id = Prompt_User_Handling::create_from_email( $subscriber['email'] );
			wp_update_user( array(
				'ID' => $subscriber_id,
				//'first_name' => $subscriber['firstname'],
				//'last_name' => $subscriber['lastname'],
			) );
		} else {
			$subscriber_id = $existing_user->ID;
		}

		$this->target_list->subscribe( $subscriber_id );

		$prompt_user = new Prompt_User( $subscriber_id );

		$origin = new Prompt_Subscriber_Origin( array(
			'source_label' => 'MailChimp Import',
			'source_url' => scbUtil::get_current_url(),
		) );

		$prompt_user->set_subscriber_origin( $origin );

		$this->imported_count++;
	}
}
