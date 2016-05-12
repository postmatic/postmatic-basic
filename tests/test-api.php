<?php

class ApiTest extends Prompt_MockMailerTestCase {

	function testSubscribeNew() {

		$subscriber_data = array(
			'email_address' => 'test@example.tld',  // Required
			'first_name'    => 'Example',           // Optional
			'last_name'     => 'User',              // Optional
		);

		$this->mail_data->subscriber_data = $subscriber_data;

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyNewSubscriberEmail' ) );

		$status = Prompt_Api::subscribe( $subscriber_data );

		$this->assertEquals( Prompt_Api::OPT_IN_SENT, $status );

		$this->assertFalse(
			get_user_by( 'email', $subscriber_data['email_address'] ),
			'Expected no user with test email address.'
		);
	}

	function verifyNewSubscriberEmail() {

		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals(
			$this->mail_data->subscriber_data['email_address'],
			$values[0]['to_address'],
			'Expected email to new subscriber.'
		);

	}

	function testSubscribeUser() {

		$user = $this->factory->user->create_and_get();
		$this->mail_data->user = $user;

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUserSubscriberEmail' ) );

		$status = Prompt_Api::subscribe( array( 'user_email' => $user->user_email ) );

		$this->assertEquals( Prompt_Api::CONFIRMATION_SENT, $status );

	}

	function testSubscribeUserToLists() {

		$lists = array( 'site', 'site_comments' );

		$user = $this->factory->user->create_and_get();
		$this->mail_data->user = $user;

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUserSubscriberEmail' ) );

		$status = Prompt_Api::subscribe( array( 'email_address' => $user->user_email ), $lists );

		$this->assertEquals( Prompt_Api::OPT_IN_SENT, $status );
	}

	function verifyUserSubscriberEmail() {

		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals(
			$this->mail_data->user->user_email,
			$values[0]['to_address'],
			'Expected email to user subscriber.'
		);

		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertEquals(
			Prompt_Enum_Message_Types::SUBSCRIPTION,
			$template['message_type'],
			'Expected subscription message type.'
		);
	}

	function testSubscribeExisting() {
		$user = $this->factory->user->create_and_get();

		$site = new Prompt_Site();
		$site->subscribe( $user->ID );

		$this->mailer_expects = $this->never();

		$status = Prompt_Api::subscribe( array( 'email_address' => $user->user_email ) );

		$this->assertEquals( Prompt_Api::ALREADY_SUBSCRIBED, $status );
	}

	function testSubscribeInvalid() {

		$this->mailer_expects = $this->never();

		$status = Prompt_Api::subscribe( array( 'email_address' => 'wtf' ) );

		$this->assertEquals( Prompt_Api::INVALID_EMAIL, $status );
	}

	function testUnsubscribe() {
		$user = $this->factory->user->create_and_get();
		$this->mail_data->user = $user;

		$site = new Prompt_Site();
		$site->subscribe( $user->ID );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUserSubscriberEmail' ) );

		$status = Prompt_Api::unsubscribe( $user->user_email );

		$this->assertEquals( Prompt_Api::CONFIRMATION_SENT, $status );
	}

	function testUnsubscribeFromList() {
		
		$user = $this->factory->user->create_and_get();
		$this->mail_data->user = $user;
		
		$site = new Prompt_Site();
		$site->subscribe( $user->ID );

		$prompt_author = new Prompt_User( $this->factory->user->create_and_get() );
		$prompt_author->subscribe( $user->ID );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyUserSubscriberEmail' ) );

		$status = Prompt_Api::unsubscribe( $user->user_email, 'user/' . $prompt_author->id() );

		$this->assertEquals( Prompt_Api::CONFIRMATION_SENT, $status );
	}

	function testNeverSubscribed() {

		$this->mailer_expects = $this->never();

		$status = Prompt_Api::unsubscribe( 'wtf' );

		$this->assertEquals( Prompt_Api::NEVER_SUBSCRIBED, $status );
	}

	function testAlreadyUnsubscribed() {
		$user = $this->factory->user->create_and_get();

		$this->mailer_expects = $this->never();

		$status = Prompt_Api::unsubscribe( $user->user_email );

		$this->assertEquals( Prompt_Api::ALREADY_UNSUBSCRIBED, $status );
	}

}