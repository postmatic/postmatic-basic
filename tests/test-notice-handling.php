<?php

class NoticeHandlingTest extends Prompt_MockMailerTestCase {

	function testNoNoticeByDefault() {
		ob_start();
		Prompt_Admin_Notice_Handling::display();
		$this->assertEmpty( ob_get_clean(), 'Expected no output.' );
	}

	function testUpgradeNotice() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $admin_id );
		}
		wp_set_current_user( $admin_id );
		Prompt_Core::$options->set( 'upgrade_required', true );

		ob_start();
		Prompt_Admin_Notice_Handling::display();
		$content = ob_get_clean();

		$this->assertContains( 'plugin_status=upgrade', $content, 'Expected a link to plugin upgrades.' );

		wp_set_current_user( 0 );
	}

	function testServiceNotice() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		Prompt_Core::$options->set( 'service_notices', array( 'foo' => 'XXBARXX' ) );

		ob_start();
		Prompt_Admin_Notice_Handling::display();
		$content = ob_get_clean();

		$this->assertContains( 'XXBARXX', $content, 'Expected the service notice message.' );

		wp_set_current_user( 0 );
	}
	
	function testWhatsNewNotice() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		Prompt_Core::$options->set( 'whats_new_notices', array( 'foo' => 'XXBARXX' ) );
	
		ob_start();
		Prompt_Admin_Notice_Handling::display();
		$content = ob_get_clean();

		$this->assertContains( 'XXBARXX', $content, 'Expected the service notice message.' );
		$this->assertContains( 'postmatic-dismiss', $content, 'Expected a disiss link.' );

		wp_set_current_user( 0 );
	}

	function testDismissNotice() {

		Prompt_Core::$options->set( 'whats_new_notices', array( 'foo' => 'XXBARXX' ) );

		$_GET['postmatic_dismiss_notice'] = 'foo';

		Prompt_Admin_Notice_Handling::dismiss();

		$this->assertContains( 'foo', \Prompt_Core::$options->get( 'skip_notices' ) );
	}

}
