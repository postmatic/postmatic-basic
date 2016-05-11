<?php

class Prompt_Admin_Subscribe_Reloaded_Import {

	/** @var  array */
	protected $subscribers;
	/** @var int */
	protected $already_subscribed_count = 0;
	/** @var int */
	protected $imported_count = 0;
	/** @var  array  */
	protected $posts;
	/** @var string  */
	private $marker_key = 'prompt_core_comments_reloaded_import_marker';
	/** @var int  */
	protected $batch_size = 500;
	/** @var int  */
	private $first;
	/** @var int  */
	private $next;
	/** @var int  */
	protected $total;
	/** @var int  */
	protected $done;
	/** @var int  */
	protected $remaining;

	/**
	 * Run the import
	 */
	public function execute() {
		$this->get_posts();
		if ( ! is_null( $this->posts ) ) {
			foreach( $this->posts as $post_id => $subscriber_emails ) {
				$this->proccess_post( $post_id, $subscriber_emails );
			}

			$this->update_counts();
		}

	}

	/**
	 * Get posts with subscribers
	 *
	 * @return array Keyed by post ID, containing emails.
	 */
	protected function get_posts() {
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT `post_id`, `meta_key`, `meta_value` FROM $wpdb->postmeta WHERE `meta_key` LIKE %s ORDER BY `meta_id` DESC",
			"%_stcr@_%"
		);
		$rows = $wpdb->get_results( $query , OBJECT );


		if ( is_array( $rows ) && ! empty( $rows ) ) {
			$this->first = (int) get_option( $this->marker_key, 0 );
			$this->total = count( $rows );
			$max = $this->first + $this->batch_size;
			$this->next = $max + 1;
			$rows = array_slice( $rows, $this->first, $max );
			$posts = array();
			foreach ( $rows as $row ) {
				if ( strpos( $row->meta_value, 'C' ) ) {
					continue;
				}

				$email                    = str_replace( '_stcr@_', '', $row->meta_key );
				if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$posts[ $row->post_id ][] = $email;
				}

			}

			$this->posts = $posts;



		}


	}

	/**
	 * Process a post's subscribers
	 *
	 * @param int $post_id The post ID
	 * @param array $subscribers Emails or subscribers
	 */
	protected function proccess_post( $post_id, $subscribers ) {
		if ( is_array( $subscribers ) && ! empty( $subscribers ) ) {
			$object = new Prompt_Post( $post_id );
			if ( is_array( $subscribers ) && ! empty( $subscribers ) ) {
				foreach( $subscribers as $subscriber ) {
					$this->import( $subscriber, $object );
				}

			}

		}
	}

	/**
	 * Import a user.
	 *
	 * @param string $subscriber The subscriber's email address
	 * @param object|\Prompt_Post Post subscribe object for current post.
	 */
	protected function import( $subscriber, $object ) {

		$existing_user = get_user_by( 'email', $subscriber );

		if ( $existing_user and $object->is_subscribed( $existing_user->ID ) ) {
			$this->already_subscribed_count++;
			return;
		}

		if ( !$existing_user ) {
			$subscriber_id = Prompt_User_Handling::create_from_email( $subscriber );
		} else {
			$subscriber_id = $existing_user->ID;
		}

		$subscribed = $object->subscribe( $subscriber_id );

		$prompt_user = new Prompt_User( $subscriber_id );

		$origin = new Prompt_Subscriber_Origin( array(
			'source_label' => 'Subscribe 2 Comments Reloaded Import',
			'source_url' => scbUtil::get_current_url(),
		) );

		$prompt_user->set_subscriber_origin( $origin );

		$this->imported_count++;

	}

	protected function update_counts() {
		$did = $this->first + $this->batch_size;
		if ( $did > $this->total) {
			$this->done = true;
			delete_option( $this->marker_key );
		}else{
			$this->done = false;
			$this->remaining = $this->total - $did;
			update_option( $this->marker_key, $this->next );
		}
	}

	public function get_error() {
		return null;
	}

	public function get_subscriber_count() {
		return count( $this->subscribers );
	}

	public function get_imported_count() {
		return $this->imported_count;
	}

	public function get_already_subscribed_count() {
		return $this->already_subscribed_count;
	}

	public function get_done() {
		return $this->done;
	}

	public function get_remaining() {
		return $this->remaining;
	}

	public function get_total() {
		return $this->total;
	}

	public function get_batch_size() {
		return $this->batch_size;
	}
}
