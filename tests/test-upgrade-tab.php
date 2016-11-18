<?php

class UpgradeOptionsTabTest extends PHPUnit_Framework_TestCase {

	function testRender() {

		$options_mock = $this->getMockBuilder( 'Prompt_Options' )->disableOriginalConstructor()->getMock();

		$tab = new Prompt_Admin_Upgrade_Options_Tab( $options_mock );

		$content = $tab->render();

		$this->assertContains( 'upgrade-options-promo', $content );

	}

}
