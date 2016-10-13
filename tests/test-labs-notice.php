<?php

class LabsNoticeTest extends WP_UnitTestCase {

	function test_display() {

		$admin = $this->factory->user->create_and_get( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin->ID );

		$test_url = 'http://foo';
		$page_mock = $this->getMock( 'Prompt_Admin_Options_Page', array(), array(), '', false );
		$page_mock->expects( $this->once() )
			->method( 'is_current_page' )
			->willReturn( false );
		$page_mock->expects( $this->once() )
			->method( 'url' )
			->willReturn( $test_url );

		$options_mock = $this->getMock( 'Prompt_Options', array(), array(), '', false );
		$options_mock->expects( $this->once() )
			->method( 'set' )
			->with( 'skip_download_intro', false );

		$notice = new Prompt_Admin_Labs_Notice( 'foo', $page_mock, $options_mock );
		ob_start();
		$notice->maybe_display();
		$content = ob_get_clean();
		$this->assertContains( $test_url, $content, 'Expected settings page URL in content.' );
	}

	function test_display_without_key() {
		$admin = $this->factory->user->create_and_get( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin->ID );
		$page_mock = $this->getMock( 'Prompt_Admin_Options_Page', array(), array(), '', false );
		$page_mock->expects( $this->never() )
			->method( 'is_current_page' );

		$notice = new Prompt_Admin_Labs_Notice( '', $page_mock );
		ob_start();
		$notice->maybe_display();
		$content = ob_get_clean();
		$this->assertEmpty( $content, 'Expect no content without a key' );
	}

	function test_display_as_author() {
		$admin = $this->factory->user->create_and_get( array( 'role' => 'author' ) );
		wp_set_current_user( $admin->ID );
		$page_mock = $this->getMock( 'Prompt_Admin_Options_Page', array(), array(), '', false );
		$page_mock->expects( $this->never() )
			->method( 'is_current_page' );

		$notice = new Prompt_Admin_Labs_Notice( 'foo', $page_mock );
		ob_start();
		$notice->maybe_display();
		$content = ob_get_clean();
		$this->assertEmpty( $content, 'Expect no content without a key' );
	}

	function test_display_on_settings_page() {
		$admin = $this->factory->user->create_and_get( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin->ID );
		$page_mock = $this->getMock( 'Prompt_Admin_Options_Page', array(), array(), '', false );
		$page_mock->expects( $this->once() )
			->method( 'is_current_page' )
			->willReturn( true );

		$notice = new Prompt_Admin_Labs_Notice( 'foo', $page_mock );
		ob_start();
		$notice->maybe_display();
		$content = ob_get_clean();
		$this->assertEmpty( $content, 'Expect no content without a key' );
	}

}
