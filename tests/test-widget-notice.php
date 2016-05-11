<?php

class WidgetNoticeTest extends WP_UnitTestCase {

	protected static $option_key = 'skip_widget_intro';

	/** @var Prompt_Admin_Widget_Notice */
	protected $notice;

	function setUp() {
		parent::setUp();
		$this->notice = new Prompt_Admin_Widget_Notice();
	}

	function tearDown() {
		Prompt_Core::$options->reset();
	}

	function testProcessDismissal() {
		$this->notice->process_dismissal();

		$this->assertFalse( Prompt_Core::$options->get( self::$option_key ), 'Expected dimiss option to be false.' );

		$_GET[self::$option_key] = true;
		$this->notice->process_dismissal();

		$this->assertTrue( Prompt_Core::$options->get( self::$option_key ), 'Expected dimiss option to be true.' );
	}

	function testIsDismissed() {

		$this->assertFalse( $this->notice->is_dismissed(), 'Expected notice not to be dismissed by default.' );

		Prompt_Core::$options->set( self::$option_key, true );

		$this->assertTrue( $this->notice->is_dismissed(), 'Expected widget to be dismissed after option set.' );
	}

	function testDismiss() {

		$this->notice->dismiss();

		$this->assertTrue( Prompt_Core::$options->get( self::$option_key ), 'Expected dimiss option to be true.' );
	}

	function testNoDisplayIfNotCapable() {
		ob_start();
		$this->notice->maybe_display();
		$content = ob_get_clean();

		$this->assertNotRegExp( '/class="notice error".*widget/', $content, 'Expected NO widget intro.' );
	}

	function testDisplayForAdmin() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		ob_start();
		$this->notice->maybe_display();
		$content = ob_get_clean();

		$this->assertRegExp( '/class="notice error".*widget/', $content, 'Expected the widget intro.' );

		wp_set_current_user( 0 );
	}

}