<?php

class SubscriptionAgreementWpMailerTest extends WP_UnitTestCase {

	function testSchedule() {
		$client_mock = $this->getMock( 'Prompt_Api_Client' );
		$client_mock->expects( $this->once() )
			->method( 'post_instant_callback' )
			->will( $this->returnCallback( array( $this, 'verifyCallback' ) ) );

		$this->site = new Prompt_Site();
		$batch = new Prompt_Subscription_Agreement_Email_Batch( $this->site );

		$mailer = new Prompt_Subscription_Agreement_Wp_Mailer( $batch, $client_mock );

		$mailer->schedule();
	}

	function verifyCallback( $data ) {
		$this->assertArrayHasKey( 'metadata', $data );
		$this->assertEquals( 'prompt/subscription_mailing/send_cached_invites', $data['metadata'][0] );
		$cached_data = get_option( $data['metadata'][1][0] );
		$this->assertNotEmpty( $cached_data, 'Expected data cached with the given key.' );

		$this->assertCount( 1, $cached_data[0], 'Expected one list.' );
		$this->assertInstanceOf( 'Prompt_Site', $cached_data[0][0] );
		$this->assertEquals( $this->site->id(), $cached_data[0][0]->id() );
		$this->assertEmpty( $cached_data[1] );
		$this->assertEmpty( $cached_data[2] );
		$this->assertEquals( 0, $cached_data[3] );
	}
}
