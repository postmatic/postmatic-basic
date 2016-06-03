<?php

class SubscriptionAgreementWpMailerTest extends WP_UnitTestCase {

	function testSchedule() {
		$client_mock = $this->getMock( 'Prompt_Api_Client' );
		$client_mock->expects( $this->once() )
			->method( 'post_instant_callback' )
			->will( $this->returnCallback( array( $this, 'verifyCallback' ) ) );

		$this->site = new Prompt_Site();
		
		$this->message = array(
			'subject' => 'Test Invite',
			'message_type' => Prompt_Enum_Message_Types::INVITATION,
		);
		
		$this->user_data = array( array( 'user_email' => "invitee@example.com", 'display_name' => "Invitee" ) );
		
		$batch_mock = $this->getMock(
			'Prompt_Subscription_Agreement_Email_Batch',
			array(),
			array( $this->site, $this->message )
		);
		$batch_mock->expects( $this->any() )
			->method( 'get_lists' )
			->willReturn( array( $this->site ) );
		$batch_mock->expects( $this->any() )
			->method( 'get_users_data' )
			->willReturn( $this->user_data );
		$batch_mock->expects( $this->any() )
			->method( 'get_message_data' )
			->willReturn( $this->message );

		$mailer = new Prompt_Subscription_Agreement_Wp_Mailer( $batch_mock, $client_mock );

		$mailer->schedule();
	}

	function verifyCallback( $data ) {
		$this->assertArrayHasKey( 'metadata', $data );
		$this->assertEquals( 'prompt/subscription_mailing/send_cached_invites', $data['metadata'][0] );
		
		$batch_key = str_replace( 'prompt_ac_', '', $data['metadata'][1][0] );
		$cached_data = get_option( $data['metadata'][1][0] );
		$this->assertNotEmpty( $cached_data, 'Expected data cached with the given key.' );

		$this->assertCount( 1, $cached_data[0], 'Expected one list.' );
		$this->assertInstanceOf( 'Prompt_Site', $cached_data[0][0] );
		$this->assertEquals( $this->site->id(), $cached_data[0][0]->id() );
		$this->assertEquals( $this->user_data, $cached_data[1] );
		$this->assertEquals( $this->message, $cached_data[2] );
		$this->assertGreaterThan( -1, $cached_data[3] );

		$delivery = get_option( 'prompt_agreement_delivery' );
		$this->assertLessThan( 
			$cached_data[3], 
			$delivery[$batch_key], 
			'Expected the recorded delivered chunk to be less the cached chunk.' 
		);
	}

}
