<?php

class CoreOptionsTabTest extends WP_UnitTestCase {

	function testRender() {

		$tab = new Prompt_Admin_Core_Options_Tab( Prompt_Core::$options );


		$content = $tab->render();

		$this->assertContains( 'core-options-promo', $content );
		$this->assertContains( 'intro-text', $content );

	}

}
