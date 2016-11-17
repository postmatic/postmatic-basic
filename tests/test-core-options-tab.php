<?php

class CoreOptionsTabTest extends WP_UnitTestCase {

	function testRender() {

		$tab = new Prompt_Admin_Core_Options_Tab( Prompt_Core::$options );


		$content = $tab->render();

		$this->assertContains( 'core-options-promo', $content );
		$this->assertContains( 'intro-text', $content );

	}

	function testValidate() {

		$old_data = array(
			'enable_comment_delivery' => true,
		);

		$new_data = array();

		$expected_data = array(
			'enable_comment_delivery' => false,
		);

		$tab = new Prompt_Admin_Core_Options_Tab( Prompt_Core::$options );

		$validated_data = $tab->validate( $new_data, $old_data );

		$this->assertEmpty( array_diff_assoc( $expected_data, $validated_data ), 'Did not get expected validated data.' );
	}

}
