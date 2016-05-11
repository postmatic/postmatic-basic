<?php

class Prompt_Admin_Jetpack_Import {

	/** @var  string */
	protected $dashboard_server_url;
	/** @var  int */
	protected $blog_id;
	/** @var  callable */
	protected $remote_request;
	/** @var  string */
	protected $api_version;
	/** @var  string */
	protected $version;
	/** @var  string */
	protected $master_user;
	/** @var  int */
	protected $subscriber_count = null;
	/** @var int */
	protected $page_count = null;
	/** @var int */
	protected $page_index = 0;
	/** @var  array */
	protected $subscribers = array();
	/** @var int */
	protected $imported_count = 0;
	/** @var int */
	protected $already_subscribed_count = 0;
	/** @var WP_Error */
	protected $error = null;

	/**
	 * @return bool
	 */
	public static function is_usable() {
		return !self::not_usable_message();
	}

	/**
	 * @return string
	 */
	public static function not_usable_message() {
		if ( !class_exists( 'DOMDocument' ) or !function_exists( 'simplexml_import_dom' ) )
			return __(
				'The Jetpack import requires a common PHP extension called "libxml" that is not present. Please check with your hosting provider.',
				'Postmatic'
			);

		if ( !defined( 'STATS_VERSION' ) or !function_exists( 'stats_get_option' ) )
			return __(
				'Before you can import Jetpack subscribers, the Jetpack plugin and its Stats module must be active.',
				'Postmatic'
			);

		return '';
	}

	public static function make() {

		if ( !self::is_usable() )
			return null;

		return new Prompt_Admin_Jetpack_Import(
			stats_get_option( 'blog_id' ),
			'http://' . STATS_DASHBOARD_SERVER . '/wp-admin/index.php',
			array( 'Jetpack_Client', 'remote_request' ),
			JETPACK__API_VERSION,
			JETPACK__VERSION,
			JETPACK_MASTER_USER
		);
	}

	public function __construct(
		$blog_id,
		$dashboard_server_url,
		$remote_request,
		$api_version,
		$version,
		$master_user
	) {
		$this->blog_id = $blog_id;
		$this->dashboard_server_url = $dashboard_server_url;
		$this->remote_request = $remote_request;
		$this->api_version = $api_version;
		$this->version = $version;
		$this->master_user = $master_user;
	}

	public function get_subscriber_count() {

		if ( !$this->subscriber_count )
			$this->fetch_next_page();

		return $this->subscriber_count;
	}

	public function get_imported_count() {
		return $this->imported_count;
	}

	public function get_already_subscribed_count() {
		return $this->already_subscribed_count;
	}

	/**
	 * @return WP_Error null if no error
	 */
	public function get_error() {
		return $this->error;
	}

	public function execute() {
		
		do {
			$this->fetch_next_page();
		} while( !$this->error and $this->page_index <= $this->page_count );

		foreach ( $this->subscribers as $subscriber ) {
			$this->import( $subscriber );
		}

	}

	protected function import( $subscriber ) {

		$existing_user = get_user_by( 'email', $subscriber['email_address'] );

		$prompt_site = new Prompt_Site();

		if ( $existing_user and $prompt_site->is_subscribed( $existing_user->ID ) ) {
			$this->already_subscribed_count++;
			return;
		}

		if ( $existing_user )
			$subscriber_id = $existing_user->ID;
		else
			$subscriber_id = Prompt_User_Handling::create_from_email( $subscriber['email_address'] );

		$prompt_site->subscribe( $subscriber_id );

		$prompt_user = new Prompt_User( $subscriber_id );

		$origin = new Prompt_Subscriber_Origin( array(
			'source_label' => 'Jetpack Import',
			'source_url' => scbUtil::get_current_url(),
		) );

		$prompt_user->set_subscriber_origin( $origin );

		$this->imported_count++;

	}

	protected function fetch_next_page() {

		$this->page_index++;

		$args = array(
			'noheader' => 'true',
			'proxy' => '',
			'page' => 'stats',
			'blog' => $this->blog_id,
			'charset' => get_option( 'blog_charset' ),
			'color' => get_user_option( 'admin_color' ),
			'ssl' => is_ssl(),
			'j' => sprintf( '%s:%s', $this->api_version, $this->version ),
			'blog_subscribers' => 0,
			'type' => 'email',
			'pagenum' => $this->page_index,
		);

		$url = add_query_arg( $args, $this->dashboard_server_url );
		$method = 'GET';
		$timeout = 90;
		$user_id = $this->master_user;

		$get = call_user_func( $this->remote_request, compact( 'url', 'method', 'timeout', 'user_id' ) );

		if ( is_wp_error( $get ) or 200 != $get['response']['code'] ) {
			$this->error = Prompt_Logging::add_error(
				'prompt_jetpack_import_http',
				__( 'A request to the Jetpack servers failed.', 'Postmatic' ),
				compact( 'get' )
			);
			return;
		}

		$dom = new DOMDocument();
		$dom->loadHTML( $get['body'] );

		$xml = simplexml_import_dom( $dom );

		if ( is_null( $this->page_count ) )
			$this->set_counts( $xml );

		$rows = $xml->xpath( "//table/tbody/tr" );

		foreach( $rows as $row ) {
			$this->subscribers[] = array(
				'email_address' => $row->td[1],
				'subscribe_date' => $row->td[3]->span['title'],
			);
		}
	}

	protected function set_counts( SimpleXMLElement $xml ) {

		$email_followers = $xml->xpath( "//ul[contains(@class,'subsubsub')]/li[a[contains(@class,'current')]]" );

		if ( empty( $email_followers ) )
			return;

		preg_match( '/\((\d+)\)$/', $email_followers[0], $user_count_matches );
		$this->subscriber_count = intval( $user_count_matches[1] );

		$this->page_count = 1;
		$page_links = $xml->xpath( "//a[contains(concat(' ',normalize-space(@class),' '), ' page-numbers ')]" );
		if ( count( $page_links ) > 1 ) {
			$last_page_link = $page_links[ count( $page_links ) - 2 ];
			$this->page_count = intval( $last_page_link );
		}
	}
}