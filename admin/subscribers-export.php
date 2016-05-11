<?php

class Prompt_Admin_Subscribers_Export {

	/** @var  array */
	protected $subscribables;

	/**
	 * Send a subscriber CSV export to an authorized user.
	 */
	public static function export_subscribers_csv() {

		if ( ! current_user_can( 'manage_options' ) )
			return;

		$export = new Prompt_Admin_Subscribers_Export( array( 'Prompt_Site' ) );

		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename=postmatic-export.csv');
		header('Pragma: no-cache');

		echo $export->csv();

		exit;
	}

	/**
	 * @param array $subscribables Subscribable types to export.
	 */
	public function __construct( $subscribables ) {
		$this->subscribables = $subscribables;
	}

	/**
	 * Get subscribers in CSV format.
	 * @return string
	 */
	public function csv() {

		$ids = array();

		foreach ( $this->subscribables as $subscribable ) {
			$ids = array_unique( array_merge( $ids, call_user_func( array( $subscribable, 'all_subscriber_ids' ) ) ) );
		}

		$csv_output = $this->field_headers();

		foreach ( $ids as $id ) {
			$user = get_user_by( 'id', $id );
			$csv_output .= implode( ',', $this->field_values( $user ) ) . "\n";
		}

		return $csv_output;
	}

	protected function field_headers() {
		return "Email Address, First Name, Last Name, Origin Date, Origin Label, Origin URL\n";
	}

	protected function field_values( $user ) {

		$prompt_user = new Prompt_User( $user );

		$origin = $prompt_user->get_subscriber_origin();

		if ( !$origin )
			$origin = new Prompt_Subscriber_Origin( array( 'timestamp' => strtotime( $user->user_registered ) ) );

		$fields = array(
			$user->user_email,
			$user->first_name,
			$user->last_name,
			$origin->get_date( 'c' ),
			$origin->get_source_label(),
			$origin->get_source_url(),
		);

		return $this->quote( $fields );
	}
	protected function quote( $fields ) {
		$quoted_fields = array();
		foreach ( $fields as $field ) {
			$quoted_fields[] = '"' . str_replace( '"', '""', $field ) . '"';
		}
		return $quoted_fields;
	}
}