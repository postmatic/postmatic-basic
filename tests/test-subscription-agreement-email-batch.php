<?php

class SubscriptionAgreementEmailBatchTest extends WP_UnitTestCase {

	function testDefaults() {

		$lists = array( new Prompt_Site() );

		$content = 'XXCONTENTXX';
		$message = '<strong>' . $content . '</strong>';

		$batch = new Prompt_Subscription_Agreement_Email_Batch( $lists, array( 'invite_introduction' => $message ) );

		$template = $batch->get_batch_message_template();

		$this->assertContains( $lists[0]->subscription_object_label(), $template['subject'] );
		$this->assertContains( '{{name_prefix}}', $template['subject'] );
		$this->assertContains( $message, $template['html_content'] );
		$this->assertContains( '{{{reply_to}}}', $template['html_content'] );
		$this->assertContains( $content, $template['text_content'] );
		$this->assertContains( '{{{reply_to}}}', $template['text_content'] );
		$this->assertEquals( get_option('blogname'), $template['from_name'] );
		$this->assertEquals( Prompt_Enum_Message_Types::SUBSCRIPTION, $template['message_type'] );
		$this->assertEquals( '{{{reply_to}}}', $template['reply_to'] );
		$this->assertContains(
			get_option( 'blogname' ),
			$template['footnote_html'],
			'Expected site title in footnote.'
		);
		$this->assertContains(
			get_option( 'blogname' ),
			$template['footnote_text'],
			'Expected site title in footnote.'
		);

		$this->assertEmpty( $batch->get_individual_message_values(), 'Expected no individual values.' );
	}

	function testAddRecipient() {

		$site_mock = $this->getMock( 'Prompt_Site' );
		$site_mock->expects( $this->any() )->method( 'subscription_object_label' )->willReturn( 'LIST' );

		$batch = new Prompt_Subscription_Agreement_Email_Batch( array( $site_mock ) );

		$user_data = array(
			'display_name' => 'TEST DUDE',
			'user_email' => 'test@example.com',
		);

		$mock_command = $this->getMock( 'Prompt_Register_Subscribe_Command' );
		$mock_command->expects( $this->once() )
			->method( 'get_keys' )
			->willReturn( array( 1 ) );

		$batch->add_agreement_recipient( $user_data, $mock_command );

		$values = $batch->get_individual_message_values();

		$this->assertCount( 1, $values );

		$this->assertEquals(
			'TEST DUDE - ',
			$values[0]['name_prefix'],
			'Expected recipient test name prefix.'
		);
		$this->assertEquals(
			'test@example.com',
			$values[0]['to_address'],
			'Expected recipient email address.'
		);
	}

	function testAddInvalidRecipient() {

		$site_mock = $this->getMock( 'Prompt_Site' );
		$site_mock->expects( $this->any() )->method( 'subscription_object_label' )->willReturn( 'LIST' );

		$batch = new Prompt_Subscription_Agreement_Email_Batch( array( $site_mock ) );

		$user_data = array(
			'display_name' => 'TEST DUDE',
			'user_email' => 'test@example.com.',
		);

		$mock_command = $this->getMock( 'Prompt_Register_Subscribe_Command' );
		$mock_command->expects( $this->never() )->method( 'get_keys' );

		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		
		$result = $batch->add_agreement_recipient( $user_data, $mock_command );
		
		$this->assertInstanceOf( 'WP_Error', $result );

		$values = $batch->get_individual_message_values();

		$this->assertCount( 0, $values );
	}

}