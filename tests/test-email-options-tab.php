<?php

class EmailOptionsTabTest extends WP_UnitTestCase {

	function testRenderDefault() {

		$tab = new Prompt_Admin_Email_Options_Tab( Prompt_Core::$options );

		$content = $tab->render();

		$this->assertContains( 'email_header_text', $content );
		$this->assertContains( 'email_footer_text', $content );
		$this->assertContains( 'send_test_email_button', $content );
		$this->assertContains( 'stylify_button', $content );
		$this->assertContains( 'subscribed_introduction', $content );
        $this->assertNotContains( 'email_footer_credit', $content );

	}

	function testRenderApiTransport() {
	    $options_mock = $this->getMockBuilder( 'Prompt_Options' )->disableOriginalConstructor()->getMock();
	    $options_mock->expects( $this->any() )->method( 'is_api_transport' )->willReturn( true );

        $tab = new Prompt_Admin_Email_Options_Tab( $options_mock );

        $content = $tab->render();

        $this->assertContains( 'email_footer_credit', $content );
    }

	function testValidate() {

		$old_data = array(
			'subscribed_introduction' => '<p>old</p>',
		);

		$new_data = array(
			'subscribed_introduction' => '<p>new</p>',
		);

		$expected_data = array(
			'subscribed_introduction' => '<p>new</p>',
		);

		$tab = new Prompt_Admin_Email_Options_Tab( Prompt_Core::$options );

		$validated_data = $tab->validate( $new_data, $old_data );

		$this->assertEmpty( array_diff_assoc( $expected_data, $validated_data ), 'Did not get expected validated data.' );
	}

}
