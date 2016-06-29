<?php

class FactoryTest extends WP_UnitTestCase {
	
	function test_default_subscription_agreement_wp_mailer() {
		
		$verifier = $this->getMock( 'Mock', array( 'verify' ) );
		$verifier->expects( $this->once() )
			->method( 'verify' )
			->willReturnCallback( array( $this, 'verify_default_subscription_agreement_wp_mailer' ) );
		
		add_filter( 'prompt/make_mailer', array( $verifier, 'verify' ), 10, 4 );
		
		$batch = new Prompt_Subscription_Agreement_Email_Batch( Prompt_Subscribing::get_signup_lists() );
		
		$mailer = Prompt_Factory::make_mailer( $batch );
		
		$this->assertInstanceOf( 'Prompt_Subscription_Agreement_Wp_Mailer', $mailer );
		
		remove_filter( 'prompt/make_mailer', array( $verifier, 'verify' ) );
	}
	
	function verify_default_subscription_agreement_wp_mailer( $mailer, $batch, $transport, $chunk ) {
		$this->assertInstanceOf( 'Prompt_Subscription_Agreement_Email_Batch', $batch );
		$this->assertNull( $transport );
		$this->assertEquals( 0, $chunk );
		return $mailer;
	}
}