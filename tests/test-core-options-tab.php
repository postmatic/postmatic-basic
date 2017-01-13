<?php

class CoreOptionsTabTest extends WP_UnitTestCase {

	function testRenderWelcome() {
		$options_mock = $this->getMockBuilder( 'Prompt_Options' )->disableOriginalConstructor()->getMock();

		$license_status_mock = $this->getMockBuilder( 'Prompt_Interface_License_Status' )
			->setMethods( array( 'is_trial_available', 'is_trial_underway', 'is_paying', 'is_pending_activation' ) )
			->getMock();

		$license_status_mock->expects( $this->once() )
			->method( 'is_trial_available' )
			->willReturn( true );

		$tab = new Prompt_Admin_Core_Options_Tab( $options_mock, null, $license_status_mock );

		$content = $tab->render();

		$this->assertContains( 'core-options-promo', $content );
		$this->assertContains( 'intro-text', $content );

	}

}
