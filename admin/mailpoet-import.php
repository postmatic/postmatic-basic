<?php

/**
 * Mailpoet import
 *
 * @package Postmatic
 * @since   1.0.0
 */
class Prompt_Admin_Mailpoet_Import {

	/** @var  Prompt_Interface_Subscribable */
	protected $target_list;
	/** @var  WYSIJA_model_user */
	protected $user_model;
	/** @var  array */
	protected $mailpoet_list_ids;
	/** @var  array */
	protected $subscribers;
	/** @var int */
	protected $already_subscribed_count = 0;
	/** @var int */
	protected $imported_count = 0;
	/** @var  array */
	protected $rejects;

	/**
	 * Whether the import can begin.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_ready() {
		return class_exists( 'WYSIJA' );
	}


	/**
	 * Build an import for given Mailpoet lists
	 *
	 * @since 1.0.0
	 * @param array|int $list_ids
	 * @param Prompt_Interface_Subscribable $target_list
	 * @return Prompt_Admin_Mailpoet_Import
	 */
	public static function make( $list_ids, $target_list ) {
		return new Prompt_Admin_Mailpoet_Import( WYSIJA::get( 'user', 'model' ), $list_ids, $target_list );
	}

	/**
	 * @param WYSIJA_model_user $user_model
	 * @param array|int $list_ids
	 * @param Prompt_Interface_Subscribable $target_list
	 */
	public function __construct( $user_model, $list_ids, $target_list ) {
		$this->user_model = $user_model;
		$this->mailpoet_list_ids = $list_ids;
		$this->rejects = array();
		$this->target_list = $target_list;
	}

	/**
	 * Currently not detecting errors.
	 *
	 * @since 1.0.0
	 * @return null
	 */
	public function get_error() {
		return null;
	}

	/**
	 * @since 1.0.0
	 * @return int
	 */
	public function get_subscriber_count() {
		$this->ensure_subscribers();
		return count( $this->subscribers );
	}

	/**
	 * @since 1.0.0
	 * @return int
	 */
	public function get_imported_count() {
		return $this->imported_count;
	}

	/**
	 * @since 1.0.0
	 * @return int
	 */
	public function get_already_subscribed_count() {
		return $this->already_subscribed_count;
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	public function get_rejected_subscribers() {
		return $this->rejects;
	}

	/**
	 * Run the import.
	 *
	 * @since 1.0.0
	 */
	public function execute() {
		$this->ensure_subscribers();

		foreach ( $this->subscribers as $subscriber ) {
			$this->import( $subscriber );
		}
	}

	/**
	 * Verify that we have MailPoet subscribers.
	 *
	 * If they haven't been retrieved yet, retrieve them.
	 *
	 * @since 1.0.0
	 */
	protected function ensure_subscribers() {
		if ( isset( $this->subscribers ) )
			return;

		$this->subscribers = array();

		// Enable the model to return more than 10 records. Could be fragile.
		$this->user_model->limit_pp = 1000000;

		$list_subscribers = $this->user_model->get_subscribers(
			array( 'A.email', 'A.firstname', 'A.lastname', 'A.last_opened', 'A.last_clicked', 'A.created_at' ),
			array( 'lists' => $this->mailpoet_list_ids )
		);

		foreach ( $list_subscribers as $list_subscriber ) {
			$this->add_source_subscriber( $list_subscriber );
		}
	}

	/**
	 * Add a MailPoet subscriber.
	 *
	 * @since 1.0.0
	 * @param $subscriber
	 */
	protected function add_source_subscriber( $subscriber ) {

		if ( $this->is_valid_subscriber( $subscriber ) )
			$this->subscribers[] = $subscriber;
		else
			$this->rejects[] = $subscriber;

	}

	/**
	 * Check if a MailPoet subscriber qualifies for import.
	 *
	 * @since 1.0.0
	 * @param $subscriber
	 * @return bool
	 */
	protected function is_valid_subscriber( $subscriber ) {
		if ( empty( $subscriber['created_at'] ) or empty( $subscriber['last_clicked'] ) )
			return false;

		return $subscriber['last_clicked'] > $subscriber['created_at'];
	}

	/**
	 * Import a MailPoet subscriber.
	 *
	 * @since 1.0.0
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
				'first_name' => $subscriber['firstname'],
				'last_name' => $subscriber['lastname'],
			) );
		} else {
			$subscriber_id = $existing_user->ID;
		}

		$this->target_list->subscribe( $subscriber_id );

		$prompt_user = new Prompt_User( $subscriber_id );

		$origin = new Prompt_Subscriber_Origin( array(
			'source_label' => 'Mailpoet Import',
			'source_url' => scbUtil::get_current_url(),
		) );

		$prompt_user->set_subscriber_origin( $origin );

		$this->imported_count++;
	}
}