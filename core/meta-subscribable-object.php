<?php

abstract class Prompt_Meta_Subscribable_Object implements Prompt_Interface_Subscribable {
	const SUBSCRIBED_META_KEY = 'subscribed_user_ids';

	/** @var string The WordPress meta type used for storage, e.g. 'post', 'user', 'comment' */
	protected $meta_type = 'Meta type should be supplied by a subclass.';

	/** @var int WordPress ID for the meta-enabled object */
	protected $id;

	public function subscriber_ids() {

		$ids = get_metadata( $this->meta_type, $this->id, self::SUBSCRIBED_META_KEY, true );

		if ( ! is_array( $ids ) ) {
			$ids = is_numeric( $ids ) ? array( (int) $ids ) : array();
		}

		return $ids;
	}

	public function is_subscribed( $user_id ) {
		return in_array( $user_id, $this->subscriber_ids() );
	}

	public function subscribe( $user_id ) {

		$user_id = intval( $user_id );

		if ( $user_id <= 0 ) {
			Prompt_Logging::add_error(
				'subscribe_user_invalid',
				__( 'Refused an attempt to subscribe an invalid user ID.', 'Postmatic' ),
				array( 'meta_type' => $this->meta_type, 'object_id' => $this->id, 'user_id' => $user_id )
			);
			return $this;
		}

		$subscriber_ids = $this->subscriber_ids();

		if ( !in_array( $user_id, $subscriber_ids ) ) {
			array_push( $subscriber_ids, $user_id );
			update_metadata( $this->meta_type, $this->id, self::SUBSCRIBED_META_KEY, $subscriber_ids );
			/**
			 * A new subscription has been added.
			 *
			 * @param int $subscriber_id
			 * @param Prompt_Interface_Subscribable $object The thing subscribed to.
			 */
			do_action( 'prompt/subscribed', $user_id, $this, $this->meta_type );
		}

		return $this;
	}

	public function unsubscribe( $user_id ) {

		$subscriber_ids = $this->subscriber_ids();

		if ( in_array( $user_id, $subscriber_ids ) ) {
			$subscriber_ids = array_diff( $subscriber_ids, array( $user_id ) );
			update_metadata( $this->meta_type, $this->id, self::SUBSCRIBED_META_KEY,  $subscriber_ids );
			/**
			 * A post subscription has been removed.
			 *
			 * @param int $subscriber_id
			 * @param Prompt_Interface_Subscribable $object The thing subscribed to.
			 */
			do_action( 'prompt/unsubscribed', $user_id, $this, $this->meta_type );
		}

		return $this;
	}

	/**
	 * @since 2.0.0
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	public function subscribe_prompt( $format = Prompt_Enum_Content_Types::HTML ) {
		return $this->subscription_object_label( $format );
	}

	/**
	 * Get all objects IDs a user is subscribed to.
	 *
	 * Would use a static method, but PHP 5.2 does not support inheritance for them.
	 *
	 * @param $user_id
	 * @return mixed
	 */
	protected function _subscribed_object_ids( $user_id ) {
		global $wpdb;

		// Here we decide how to deal with querying serialized data
		// Choosing to depend on PHP's serial format

		$id_field = $this->meta_type . '_id';
		$table_property = $this->meta_type . 'meta';
		$table = $wpdb->$table_property;

		$query = $wpdb->prepare(
			"SELECT {$id_field}, meta_value FROM {$table} WHERE meta_key=%s AND meta_value LIKE %s",
			self::SUBSCRIBED_META_KEY,
			'%i:%;%i:' . $user_id . ';%'
		);

		$results = $wpdb->get_results( $query );

		// The above query can still misinterpret indices for values, we must unserialize to be sure
		$ids = array();
		foreach ( $results as $result ) {
			$user_ids = unserialize( $result->meta_value );
			if ( is_array( $user_ids ) && in_array( $user_id, $user_ids, false ) ) {
				$ids[] = $result->$id_field;
			}
		}

		return $ids;
	}

	protected function _all_subscriber_ids() {
		global $wpdb;

		$table_property = $this->meta_type . 'meta';
		$table = $wpdb->$table_property;

		$sql_format = "SELECT DISTINCT {$wpdb->users}.id " .
			"FROM {$wpdb->users} " .
			"JOIN {$table} ON meta_key=%s AND meta_value LIKE CONCAT( '%%i:%%;i:', {$wpdb->users}.id, ';%%' )";

		$query = $wpdb->prepare( $sql_format, self::SUBSCRIBED_META_KEY );

		return $wpdb->get_col( $query );
	}

}