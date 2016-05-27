<?php

class CoreOptionsTabTest extends WP_UnitTestCase {

	function testRender() {

		$tab = new Prompt_Admin_Core_Options_Tab( Prompt_Core::$options );


		$content = $tab->render();

		$this->assertContains( 'core-options-promo', $content );
		$this->assertContains( 'enable_invites', $content );
		$this->assertContains( 'enable_optins', $content );
		$this->assertContains( 'enable_digests', $content );
		$this->assertContains( 'enable_notes', $content );
		$this->assertContains( 'enable_post_delivery', $content );
		$this->assertContains( 'enable_comment_delivery', $content );
		$this->assertContains( 'enable_mailchimp_import', $content );
		$this->assertContains( 'enable_mailpoet_import', $content );
		$this->assertContains( 'enable_jetpack_import', $content );
		$this->assertContains( 'enable_skimlinks', $content );
		$this->assertContains( 'enable_webhooks', $content );

		$this->assertFalse(
			Prompt_Core::$options->get( 'enable_invites' ),
			'Expected invites to be disabled without premium active.'
		);
		$this->assertFalse(
			Prompt_Core::$options->get( 'enable_webhooks' ),
			'Expected webhooks to be disabled without premium active.'
		);
	}

	function testValidate() {

		$old_data = array(
			'enable_invites' => true,
			'enable_optins' => false,
			'enable_digests' => true,
		);

		$new_data = array(
			'enable_optins' => true,
			'enable_invites' => true,
			'enable_webhooks' => true,
		);

		$expected_data = array(
			'enable_invites' => true,
			'enable_optins' => true,
			'enable_digests' => false,
			'enable_webhooks' => true,
		);

		$tab = new Prompt_Admin_Core_Options_Tab( Prompt_Core::$options );

		$validated_data = $tab->validate( $new_data, $old_data );

		$this->assertEmpty( array_diff_assoc( $expected_data, $validated_data ), 'Did not get expected validated data.' );
	}

}
