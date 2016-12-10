<?php

class CoreOptionsTabTest extends WP_UnitTestCase {

	function testRenderWelcome() {
		$options_mock = $this->getMockBuilder( 'Prompt_Options' )->disableOriginalConstructor()->getMock();

		$license_status_mock = $this->getMock( 'Prompt_Interface_License_Status', array( 'is_trial_available' ) );
		$license_status_mock->expects( $this->once() )
			->method( 'is_trial_available' )
			->willReturn( true );

		$tab = new Prompt_Admin_Core_Options_Tab( $options_mock, null, $license_status_mock );

		$content = $tab->render();

		$this->assertContains( 'core-options-promo', $content );
		$this->assertContains( 'intro-text', $content );

	}

}
