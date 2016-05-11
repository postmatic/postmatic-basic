<?php

class OptionsPageTest extends Prompt_MockMailerTestCase {

	/** @var Prompt_Admin_Options_Page $page */
	protected $page;

	function testPageLoaded() {
		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options );

		$mock_filter = $this->getMock( 'Foo', array( 'tabs' ) );
		$mock_filter->expects( $this->once() )
			->method( 'tabs' )
			->will( $this->returnCallback( array( $this, 'verifyTabs' ) ) );

		add_filter( 'prompt/options_page/tabs', array( $mock_filter, 'tabs' ) );

		$page->page_loaded();

		remove_filter( 'prompt/options_page/tabs', array( $mock_filter, 'verifyTabs' ) );
	}

	function verifyTabs( $tabs ) {
		$this->assertNotEmpty( $tabs, 'Expected some tabs' );
		$this->assertContainsOnlyInstancesOf( 'Prompt_Admin_Options_Tab', $tabs );
		return $tabs;
	}

	function testPageHead() {
		$mock_tab = $this->getMock( 'Tab_Mock', array( 'page_head' ) );
		$mock_tab->expects( $this->once() )->method( 'page_head' );

		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options, null, array( $mock_tab ) );

		$page->page_head();

		$this->assertTrue( wp_style_is( 'prompt-admin', 'enqueued' ), 'Expected admin styles to be enqueued.' );
		$this->assertTrue( wp_script_is( 'prompt-options-page', 'enqueued' ), 'Expected Prompt admin script to be enqueued.' );
	}

	function testTabFormHandler() {
		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options );

		$mock_tab = $this->getMockBuilder( 'Prompt_Admin_Options_Tab' )
			->disableOriginalConstructor()
			->setMethods( array( 'slug', 'form_handler' ) )
			->getMock();

		$slug = 'test';
		$mock_tab->expects( $this->once() )->method( 'slug' )->will( $this->returnValue( $slug ) );
		$mock_tab->expects( $this->once() )->method( 'form_handler' );

		$page->add_tab( $mock_tab );

		$_POST['tab'] = $slug;

		$page->page_loaded();
	}

	function testNoKeyContent() {
		Prompt_Core::$options->set( 'prompt_key', '' );

		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options );

		ob_start();
		$page->page_content();
		$content = ob_get_clean();

		$this->assertContains( 'id="prompt_key"', $content );
		$this->assertNotContains( 'id="prompt-tabs"', $content );

		Prompt_Core::$options->reset();
	}

	function testPageContent() {
		$key = 'test';
		Prompt_Core::$options->set( 'prompt_key', $key );
		$mock_page = $this->getNoAlertPageMock( $key );

		ob_start();
		$mock_page->page_content();
		$content = ob_get_clean();

		$this->assertContains( "id=\"prompt-tabs\"", $content );
		$this->assertContains( 'class="local-transport"', $content );

		Prompt_Core::$options->reset();
	}

	function testBugReport() {
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$_POST['error_alert'] = true;
		$_POST['submit_errors'] = true;

		$mock_page = $this->getMock(
			'Prompt_Admin_Options_Page',
			array( 'check_args' ),
			array( false, Prompt_Core::$options, null, array() )
		);
		$mock_page->expects( $this->never() )
			->method( 'form_handler' );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyBugReportEmail' ) );

		$mock_page->page_loaded();

		$dismissed_time = get_user_meta( $user_id, Prompt_Admin_Options_Page::DISMISS_ERRORS_META_KEY, true );

		$this->assertGreaterThan( time() - 60*60, $dismissed_time, 'Expected a recent bug report time.' );

		wp_set_current_user( 0 );
	}

	function verifyBugReportEmail() {
		$template = $this->mailer_payload->get_batch_message_template();

		$user = wp_get_current_user();
		$this->assertEquals(  $user->user_email, $template['from_address'], 'Expected report from the current user.' );
		$this->assertEquals(
			Prompt_Enum_Message_Types::ADMIN,
			$template['message_type'],
			'Expected admin message type.'
		);

		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals(
			Prompt_Core::SUPPORT_EMAIL,
			$values[0]['to_address'],
			'Expected the bug report to be sent to the support address.'
		);
	}

	function testRedirect() {
		Prompt_Core::$options->set( 'prompt_key', '' );
		add_filter( 'wp_redirect', array( $this, 'checkRedirect' ) );

		$admin = $this->factory->user->create_and_get( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin->ID );

		$this->mail_data->redirect_url = '';
		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options, null, array(), array() );
		$this->assertEquals( $page->url(), $this->mail_data->redirect_url, 'Expected to detect an auto load redirect.' );

		wp_set_current_user( 0 );
		remove_filter( 'wp_redirect', array( $this, 'checkRedirect' ) );
		Prompt_Core::$options->reset();
	}

	function checkRedirect( $url ) {
		$this->mail_data->redirect_url = $url;
	}

	function testValidateKey() {

		$api_mock = $this->getValidKeyApiMock();
		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options, null, array(), array(), $api_mock );
		$page->validate_key( 'foo' );

		$this->assertEquals(
			Prompt_Enum_Email_Transports::API,
			Prompt_Core::$options->get( 'email_transport' ),
			'Expected API transport to be set.'
		);

		Prompt_Core::$options->reset();
	}

	function testValidateKeyError() {
		$error = new WP_Error( 'http_request_failed', 'Couldn\'t connect to host' );
		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'get_site' )
			->will( $this->returnValue( $error ) );

		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options, null, array(), array(), $api_mock );
		$result = $page->validate_key( 'foo' );

		$this->assertEquals( $error, $result );
	}

	function testConnectionCheck() {

		Prompt_Core::$options->set( 'connection_status', false );

		$api_mock = $this->getValidKeyApiMock();
		$api_mock->expects( $this->once() )
			->method( 'post_instant_callback' )
			->will( $this->returnValue( array( 'response' => array( 'code' => 200 ) ) ) );

		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options, null, array(), array(), $api_mock );

		ob_start();
		$page->page_content();
		$result = ob_get_clean();

		$this->assertContains( 'checking-connection', $result );

		Prompt_Core::$options->reset();
	}

	function testSkipConnectionCheck() {

		Prompt_Core::$options->set( 'connection_status', Prompt_Enum_Connection_Status::CONNECTED );

		$api_mock = $this->getValidKeyApiMock();
		$api_mock->expects( $this->never() )->method( 'post_instant_callback' );

		$page = new Prompt_Admin_Options_Page( __FILE__, Prompt_Core::$options, null, array(), array(), $api_mock );

		ob_start();
		$page->page_content();
		$result = ob_get_clean();

		$this->assertNotContains( 'checking-connection', $result );

		Prompt_Core::$options->reset();
	}

	function getValidKeyApiMock() {
		$site_response = array(
			'response' => array( 'code' => 200 ),
			'body' => json_encode( array(
				'messages' => 'foo',
				'site' => array( 'url' => admin_url( 'admin-ajax.php' ) ),
				'configuration' => array( 'email_transport' => Prompt_Enum_Email_Transports::API ),
			) ),
		);
		$api_mock = $this->getMock( 'Prompt_Api_Client' );
		$api_mock->expects( $this->once() )
			->method( 'get_site' )
			->will( $this->returnValue( $site_response ) );

		return $api_mock;
	}

	function getNoAlertPageMock( $key ) {
		$mock_page = $this->getMock(
			'Prompt_Admin_Options_Page',
			array( 'validate_key', 'display_key_prompt', 'connection_alert' ),
			array( __FILE__, Prompt_Core::$options, null, array(), array() )
		);
		$mock_page->expects( $this->once() )
			->method( 'validate_key' )
			->will( $this->returnValue( $key ) );
		$mock_page->expects( $this->never() )->method( 'display_key_prompt' );
		$mock_page->expects( $this->any() )
			->method( 'connection_alert' )
			->will( $this->returnValue( '' ) );

		return $mock_page;
	}


}
