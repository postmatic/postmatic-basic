<?php

class SubscriptionAgreementWpMailerTest extends WP_UnitTestCase {

	function testSchedule() {

		$client_mock = $this->getMock( 'Prompt_Api_Client' );

		$batch_mock = $this->getMockBuilder( 'Prompt_Subscription_Agreement_Email_Batch' )
			->setConstructorArgs( array( new Prompt_Site_Comments() ) )
			->getMock();

		$mailer_mock = $this->getMockBuilder( 'Prompt_Subscription_Agreement_Wp_Mailer' )
			->setConstructorArgs( array( $batch_mock, $client_mock ) )
			->setMethods( array( 'send' ) )
			->getMock();

		$mailer_mock->expects( $this->once() )->method( 'send' );

		$mailer_mock->schedule();
	}

}
