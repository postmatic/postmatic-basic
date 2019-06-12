<?php

/**
 * Handle Prompt options and those of active add-ons.
 *
 * @since 1.0.0
 */
class Prompt_Admin_Options_Page extends scbAdminPage {
	const DISMISS_ERRORS_META_KEY = 'prompt_error_dismiss_time';

	/** @type Prompt_Options */
	protected $options;

	/** @type array */
	protected $_overridden_options;

	/** @var Prompt_Admin_Options_Tab[] */
	protected $tabs;

	/** @var  Prompt_Admin_Options_Tab */
	protected $submitted_tab;

	/** @var  Prompt_Api_Client */
	protected $api_client;

	/** @var  string shortcut for $this->options->get( 'prompt_key' ) */
	protected $key;

	/** @var  Prompt_Admin_Conditional_Notice[] */
	protected $notices;

	/** @var  Prompt_Interface_License_Status */
	protected $license_status;

	/** @var  boolean */
	protected $is_current_page = false;

	/**
	 * @since 1.0.0
	 * @param string|bool                       $file
	 * @param Prompt_Options                    $options
	 * @param array                             $overrides
	 * @param Prompt_Admin_Options_Tab[]        $tabs
	 * @param Prompt_Admin_Conditional_Notice[] $notices
	 * @param Prompt_Interface_License_Status   $license_status
	 * @param Prompt_Api_Client                 $api_client
	 */
	public function __construct(
		$file = false,
		$options = null,
		Prompt_Interface_License_Status $license_status = null,
		$overrides = null,
		$tabs = null,
		$notices = null,
		$api_client = null
	) {
		parent::__construct( $file, $options );
		$this->_overridden_options = $overrides;
		$this->key = $options->get( 'prompt_key' );

		$this->maybe_auto_load();

		$this->license_status = $license_status;

		$this->tabs = $tabs;

		$this->notices = $notices;

		$this->api_client = $api_client;
	}

	/**
	 * Set any values used in the parent class.
	 *
	 * @since 1.0.0
	 */
	public function setup() {
		$this->args = array(
			'page_title' => __( 'Replyable', 'Postmatic' ),
			'page_slug' => 'postmatic',
		);
	}

	/**
	 * Add a settings tab.
	 *
	 * @since 1.0.0
	 * @param Prompt_Admin_Options_Tab $tab
	 */
	public function add_tab( Prompt_Admin_Options_Tab $tab ) {
		if ( !$this->tabs )
			$this->tabs = array();

		$this->tabs[$tab->slug()] = $tab;
	}

	/**
	 * Before there is any output, add tabs and handle any posted options.
	 *
	 * @since 1.0.0
	 */
	public function page_loaded() {

		$this->is_current_page = true;

		if ( is_null( $this->notices ) ) {
			$this->add_notices();
		}

		foreach ( $this->notices as $notice ) {
			$notice->process_dismissal();
		}

		if ( $this->process_tabs() ) {
			return;
		}

		if ( !empty( $_POST['error_alert'] ) ) {

			if ( !empty( $_POST['delete_errors'] ) ) {
				Prompt_Logging::delete_log();
			} else {
				update_user_meta( get_current_user_id(), self::DISMISS_ERRORS_META_KEY, time() );
			}

			if ( !empty( $_POST['submit_errors'] ) ) {
				$this->submit_errors();
				return;
			}
		}

		$this->form_handler();
		$this->load_new_key();
	}

	/**
	 * Add a notice.
	 *
	 * @since 1.0.0
	 * @param string $msg
	 * @param string $class
	 */
	public function admin_msg( $msg = '', $class = 'updated' ) {
		$settings_errors = get_settings_errors();
		if ( !empty( $settings_errors ) )
			return;

		if ( empty( $msg ) )
			$msg = __( 'Settings <strong>saved</strong>.', 'Postmatic' );

		echo scb_admin_notice( $msg, $class );
	}

	/**
	 * @since 1.0.0
	 */
	public function submitted_errors_admin_msg() {
		$this->admin_msg( __( 'Report sent! Our bug munchers thank you for the meal.', 'Postmatic' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function beta_request_sent_admin_msg() {
		$this->admin_msg( __( 'Request sent. We are currently sending a few hundred tokens per week. Expect to receive yours within 1-2 days. You can safely leave Postmatic activated but it is not necessary to do so.', 'Postmatic' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function page_head() {

		add_thickbox();

		wp_enqueue_media();

		wp_enqueue_style(
			'prompt-admin',
			path_join( Prompt_Core::$url_path, 'css/admin.css' ),
			array(),
			Prompt_Core::version()
		);

		$script = new Prompt_Script( array(
			'handle' => 'prompt-options-page',
			'path' => 'js/options-page.js',
			'dependencies' => array( 'thickbox', 'wp-ajax-response' ),
		) );
		$script->enqueue();

		$download_title = Prompt_Core::is_api_transport() ? __( 'Upgrade', 'Postmatic' ) : __( 'Important information about upcoming changes to Postmatic Basic', 'Postmatic' );

		$script->localize( 'prompt_options_page_env', array(
			'download_title' => $download_title,
			'skip_download_intro' => $this->options->get( 'skip_download_intro' ),
		) );

		foreach ( $this->tabs as $tab ) {
			$tab->page_head();
		}

		if ( !$this->options->get( 'skip_download_intro' ) ) {
			$this->options->set( 'skip_download_intro', true );
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function page_header() {

		$wrapper = '<div class="wrap signup">';
		$account_url = Prompt_Enum_Urls::MANAGE;

		if ( $this->key ) {
			$wrapper = '<div class="wrap" style="display: none;">';
			$account_url .= '/login';
		}

		echo $wrapper;
		echo html( 'h2 id="prompt-settings-header"', html( 'span', $this->args['page_title'] ) );
	}

	/**
	 * @since 1.0.0
	 */
	function page_content() {

		echo $this->log_alert();

		$key_alert = $this->key_alert();
		echo $key_alert;

		$connection_alert = $this->connection_alert();

		if ( $connection_alert ) {
			echo $connection_alert;
			return;
		}

		foreach ( $this->notices as $notice ) {
			$notice->maybe_display();
		}

		list( $tabs, $panels ) = $this->tabs_content();

		echo html(
			'div id="prompt-tabs"',
			array( 'class' => $this->options->get( 'email_transport' ) . '-transport' ),
			html( 'h2 class="nav-tab-wrapper"',
				$tabs
			),
			$panels
		);

	}

	/**
	 * @since 1.0.0
	 */
	protected function key_alert() {

		// Before key is entered we don't check anything
		if ( empty( $this->key ) ) {
			return '';
		}

		// Only check key validity when viewing main settings page
		if ( isset( $_POST['tab'] ) or isset( $_POST['prompt_key'] ) ) {
			return '';
		}

		$key = $this->validate_key( $this->key );

		if ( is_wp_error( $key ) and "couldn't connect to host" == $key->get_error_message() ) {
			return html( 'div class="error"', html( 'p', $this->blocked_connection_message() ) );
		}

		if ( is_wp_error( $key ) ) {
			return html( 'div class="error"', html( 'p', $key->get_error_message() ) );
		}

		return '';
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function blocked_connection_message() {
		return __(
			'We are unable to reach the Postmatic server at app.gopostmatic.com. If this happens consistently, your web host may be blocking the connection, and you\'ll have to ask for an exception before Postmatic will work.',
			'Postmatic'
		);
	}

	/**
	 * @since 1.0.0
	 */
	protected function connection_alert() {

		if ( ! $this->key ) {
			return '';
		}

		$skip_statuses = array( Prompt_Enum_Connection_Status::CONNECTED, Prompt_Enum_Connection_Status::ISOLATED );

		if ( in_array( Prompt_Core::$options->get( 'connection_status' ), $skip_statuses ) ) {
			return '';
		}

		$response = $this->api_client()->post_instant_callback(
			array( 'metadata' => array( 'prompt/check_connection', array() ) )
		);

		if ( is_wp_error( $response ) ) {
			return html( 'div class="error"', html( 'p', $response->get_error_message() ) );
		}

		return html( 'div id="checking-connection" ',
			html( 'p',
				html( 'span', array( 'class' => 'loading-indicator' ) ),
				__( '<strong>Just a moment</strong>. Replyable is running a test to make sure our server can talk to yours. It may take a few seconds.', 'Postmatic' )
			)
		) . html( 'div id="bad-connection" style="display: none;"',
			html( 'p',
				__( '<strong>There\'s a problem :(</strong> Replyable was unable to connect to your server. If you or your web host have put restrictions on incoming web connections an exception may be needed to let our server (app.gopostmatic.com) talk to yours. Click the question mark icon in the lower right corner for more assistance or try contacting your web host.', 'Postmatic' )
			)
		);
	}


	/**
	 * @since 1.0.0
	 */
	protected function log_alert() {
		$dismiss_time = absint( get_user_meta( get_current_user_id(), self::DISMISS_ERRORS_META_KEY, true ) );

		$since = max( $dismiss_time, Prompt_Logging::get_last_submission_time() );

		$log = Prompt_Logging::get_log( $since, ARRAY_A );

		if ( empty( $log ) ) {
			return '';
		}

		$rows = array();
		foreach ( $log as $entry ) {

			$rows[] = html( 'tr',
				html( 'td', date( 'Y-m-d H:i:s e', $entry['time'] ) ),
				html( 'td', $entry['message'] ),
				html( 'td', html( 'textarea', json_encode( $entry['data'] ) ) )
			);

		}

		if ( empty( $rows ) ) {
			return '';
		}

		return html( 'div class="error"',
			html( 'form', array( 'method' => 'post', 'action' => '' ),
				html( 'p',
					__( '<strong>Attention:</strong> There have been some recent errors in your configuration. An error log can be found here: ' )
				),
				html( 'table class="wp-list-table widefat"',
					implode( '', $rows )
				),
				html( 'input', array( 'type' => 'hidden', 'name' => 'error_alert', 'value' => '1' ) ),
				get_submit_button( __( 'Dismiss', 'Postmatic' ), 'primary large', 'dismiss_errors' ),
				get_submit_button( __( 'Submit A Bug Report', 'Postmatic' ), 'left', 'submit_errors' )
			)
		);
	}

	/**
	 * @since 1.0.0
	 * @return array Tabs HTML element 0, Panels HTML element 1
	 */
	protected function tabs_content() {
		$tabs   = '';
		$panels = '';

		$submitted_slug = $this->submitted_tab ? $this->submitted_tab->slug() : '';
		foreach ( $this->tabs as $slug => $tab ) {
			$enabled = Prompt_Core::$options->get( 'enable_' . str_replace( '-', '_', $tab->slug() ) );
			$enabled = is_null( $enabled ) ? true : $enabled;
			$tabs   .= html(
				'a',
				array(
					'href'  => add_query_arg(
						array(
							'page' => 'postmatic',
							'tab'  => 'prompt-tab-content-' . $slug,
						),
						admin_url( 'options-general.php' )
					),
					'id'    => 'prompt-tab-' . $slug,
					'class' => 'nav-tab ' . ( $slug === $submitted_slug || ( '' === $submitted_slug && 'core' === $tab->slug() ) ? 'nav-tab-active' : '' ),
					'data-tab-name' => 'prompt-tab-content-' . $slug,
					'style' => $enabled ? '' : 'display: none;',
				),
				$tab->name()
			);
			$panels .= html(
				'div',
				array(
					'id'    => 'prompt-tab-content-' . $slug,
					'class' => 'prompt-tab-content hide',
				),
				$tab->render()
			);
		}

		return array( $tabs, $panels );
	}

	/**
	 * Assemble sidebar content
	 *
	 * @since 1.0.0
	 * @return string content
	 */
	protected function sidebar_content() {
		return html(
			'div id="prompt-sidebar"',
			'&nbsp;'
		);
	}

	/**
	 * @since 1.0.0
	 * @param array $new_data
	 * @param array $old_data
	 * @return array
	 */
	public function validate( $new_data, $old_data ) {
		$valid_data = $old_data;

		if ( !isset( $new_data['prompt_key'] ) or $new_data['prompt_key'] == $old_data['prompt_key'] )
			return $valid_data;

		$key = $this->validate_key( $new_data['prompt_key'] );
		if ( is_wp_error( $key ) ) {
			add_settings_error( 'prompt_key', 'invalid_key', $key->get_error_message() );
			return $valid_data;
		}

		$valid_data['prompt_key'] = $key;
		$this->key = $key;

		return $valid_data;
	}

	/**
	 * Validate a key
	 *
	 * @since 1.0.0
	 * @param string            $key
	 * @return mixed|string|WP_Error
	 */
	public function validate_key( $key ) {
		if ( empty( $key ) ) {
			return '';
		}

		$key = preg_replace( '/\s/', '', sanitize_text_field( $key ) );

		$response = $this->api_client( $key )->get_site();

		if ( is_wp_error( $response ) or !in_array( $response['response']['code'], array( 200, 401, 503 ) ) ) {
			return Prompt_Logging::add_error(
				'key_http_error',
				__( 'There\'s a problem verifying your key. Please try later or report this error.', 'Postmatic' ),
				$response
			);
		}

		if ( 503 == $response['response']['code'] ) {
			$message = sprintf(
				__(
					'Replyable service is temporarily unavailable, see <a href="%s">our twitter feed</a> for updates.',
					'Postmatic'
				),
				Prompt_Enum_Urls::TWITTER
			);
			return new WP_Error( 'service_unavailable', $message );
		}

		if ( 401 == $response['response']['code'] ) {
			$message = sprintf(
				__(
					'We didn\'t recognize the key "%s". Please make sure it exactly matches the key we supplied you. <a href="%s" target="_blank">Visit your Replyable dashboard for assistance</a>. ',
					'Postmatic'
				),
				$key,
				Prompt_Enum_Urls::MANAGE
			);
			return new WP_Error( 'invalid_key', $message );
		}

		$configuration = json_decode( $response['body'] );

		if ( !self::site_matches( $configuration->site->url ) ) {
			$message = sprintf(
				__(
					'Your key was registered for a different site. Please request a key for this site\'s dedicated use, or <a href="%s" target="_blank">contact us</a> for assistance. Thanks!',
					'Postmatic'
				),
				Prompt_Enum_Urls::BUG_REPORTS
			);
			return new WP_Error( 'wrong_key', $message );
		}

		if (
			Prompt_Core::$options->get( 'enable_digests' )
			and
			! in_array( Prompt_Enum_Message_Types::DIGEST, $configuration->configuration->enabled_message_types )
		) {
			$configuration->configuration->enable_digests = false;
		}

		$configurator = Prompt_Factory::make_configurator( $this->api_client() );

		$configurator->update_configuration( $configuration );

		return $key;
	}

	/**
	 * Get the URL of the options page.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function url() {
		return add_query_arg( 'page', $this->args['page_slug'], admin_url( $this->args['parent'] ) );
	}

	/**
	 * Whether the current request is for the options page.
	 *
	 * Only available after the load-(page) action.
	 *
	 * @since 1.3.0
	 *
	 * @return bool
	 */
	public function is_current_page() {

		if ( !did_action( 'current_screen' ) )
			trigger_error( 'is_current_page() is not available until after the load-(page) action' );

		return $this->is_current_page;
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @return Prompt_Api_Client
	 */
	protected function api_client( $key = null ) {
		if ( ! $this->api_client ) {
			$this->api_client = new Prompt_Api_Client( array(), $key );
		}
		return $this->api_client;
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param string $url
	 * @return bool
	 */
	protected function site_matches( $url ) {
		$url_parts = parse_url( strtolower( $url ) );

		if ( !isset( $url_parts['host'] ) or !isset( $url_parts['path'] ) )
			return false;

		$ajax_url_parts = parse_url( strtolower( admin_url( 'admin-ajax.php' ) ) );

		return ( $url_parts['host'] === $ajax_url_parts['host'] and $url_parts['path'] === $ajax_url_parts['path'] );
	}

	/**
	 * @since 1.0.0
	 */
	protected function submit_errors() {
		Prompt_Logging::submit();
		add_action( 'admin_notices', array( $this, 'submitted_errors_admin_msg' ) );
	}

	/**
	 * @since 1.0.0
	 */
	protected function load_new_key() {
		if ( $this->key != $this->options->get( 'prompt_key' ) ) {
			wp_redirect( $_SERVER['REQUEST_URI'] );
		}
	}

	/**
	 * Create tabs and process any tab-submitted data.
	 *
	 * @since 1.4.0
	 *
	 * @return bool whether submitted tab data was processed
	 */
	protected function process_tabs() {

		if ( is_null( $this->tabs ) ) {
			$this->add_tabs();
		}

		$did_updates = false;
		$submitted_tab_slug = isset( $_POST['tab'] ) ? $_POST['tab'] : null;

		if ( $submitted_tab_slug ) {
			$this->submitted_tab = $this->tabs[$submitted_tab_slug];
			$this->submitted_tab->form_handler();
			$this->load_new_key(); // in case a new key was saved
			$did_updates = true;
		}

		return $did_updates;
	}

	/**
	 * Create and add options tabs.
	 *
	 * @since 1.0.0
	 */
	protected function add_tabs() {

		$tabs = array(
			new Prompt_Admin_Core_Options_Tab( $this->options, $this->_overridden_options, $this->license_status ),
			new Prompt_Admin_Email_Options_Tab( $this->options, $this->_overridden_options ),
			new Prompt_Admin_Comment_Options_Tab( $this->options, $this->_overridden_options ),
			new Prompt_Admin_Subscribe_Reloaded_Import_Options_Tab( $this->options, $this->_overridden_options ),
			new Prompt_Admin_Recommended_Plugins_Options_Tab( $this->options, $this->_overridden_options ),
		);

		if ( ! $this->license_status->is_trial_underway() and ! $this->license_status->is_paying() ) {
			$tabs[] = new Prompt_Admin_Upgrade_Options_Tab( $this->options );
		}

		$tabs = apply_filters( 'prompt/options_page/tabs', $tabs );

		array_map( array( $this, 'add_tab' ), $tabs );
	}

	protected function add_notices() {
		$this->notices = array(
			new Prompt_Admin_Local_Mail_Notice(),
		);
	}

	/**
	 * When there isn't a key and the user can add one, direct them here.
	 */
	protected function maybe_auto_load() {

		if ( $this->key )
			return;

		if ( !current_user_can( $this->args['capability'] ) )
			return;

		if ( !$this->options->get( 'redirect_to_options_page' ) )
			return;

		$this->options->set( 'redirect_to_options_page', false );

		wp_redirect( $this->url() );
	}

}
