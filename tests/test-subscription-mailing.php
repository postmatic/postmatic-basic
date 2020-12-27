<?php
/**
 * Note that the ajax tests include a lot of stuff that you might expect here.
 */
class SubscriptionMailingTest extends Prompt_MockMailerTestCase {

	function testAgreement() {
		$this->mail_data->lists = array( new Prompt_Site_Comments() );
		$this->mail_data->email = 'test@example.com';
		$user_data = array();

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyAgreement' ) );

		Prompt_Subscription_Mailing::send_agreement( $this->mail_data->lists, $this->mail_data->email, $user_data );
	}

	function verifyAgreement() {
		$values = $this->mailer_payload->get_individual_message_values();

		$this->assertCount( 1, $values, 'Expected one agreement recipient.' );

		$this->assertEquals(
			$this->mail_data->email,
			$values[0]['to_address'],
			'Expected email to the agreement recipient only.'
		);

		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains(
			$this->mail_data->lists[0]->subscription_object_label(),
			$template['subject'],
			'Expected the object label in the email subject.'
		);

	}

	function setupAgreementData() {
		$this->mail_data->lists = array( new Prompt_Post( $this->factory->post->create() ), new Prompt_Site_Comments() );
		$this->mail_data->users = array(
			array( 'user_email' => 'test1@example.com' ),
			array( 'user_email' => 'test2@example.com' ),
		);
		$this->mail_data->template_data = array(
			'subject' => 'Test subject',
			'from_name' => 'Test Author',
			'invite_introduction' => 'Test message',
		);
	}

	function testAgreements() {

		$this->setupAgreementData();

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyInvites' ) );

		Prompt_Subscription_Mailing::send_agreements(
			$this->mail_data->lists,
			$this->mail_data->users,
			$this->mail_data->template_data
		);

	}

	function testCachedInvites() {

		$this->setupAgreementData();

		$test_key = 'cached_invite_test_yo';

		update_option( $test_key, array(
			$this->mail_data->lists,
			$this->mail_data->users,
			$this->mail_data->template_data,
		), false );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyInvites' ) );

		Prompt_Subscription_Mailing::send_cached_invites( $test_key );

		$this->assertFalse( get_option( $test_key ), 'Expected cached option to be deleted.' );
	}

	function verifyInvites() {

		$values = $this->mailer_payload->get_individual_message_values();

		$this->assertCount( 2, $values, 'Expected two invite emails to be sent.' );

		$this->assertEquals(
			$this->mail_data->users[0]['user_email'],
			$values[0]['to_address'],
			'Expected a test recipient.'
		);

		$template = $this->mailer_payload->get_batch_message_template();

		$this->assertEquals( $this->mail_data->template_data['from_name'], $template['from_name'] );
		$this->assertEquals(
			$this->mail_data->template_data['subject'],
			$template['subject'],
			'Expected the supplied subject.'
		);
		$this->assertContains(
			$this->mail_data->template_data['invite_introduction'],
			$template['html_content'],
			'Expected the supplied message.'
		);

		$this->assertNotEmpty( $values[0]['opt_in_url'], 'Expected an opt-in url.' );

	}

	function testWelcome() {
		Prompt_Core::$options->set( 'subscribed_introduction', 'XXWELCOMEXX' );

		$subscriber = $this->factory->user->create_and_get();
		$this->mail_data->subscriber = $subscriber;

		$object = new Prompt_Post( $this->factory->post->create() );
		$this->mail_data->object = $object;

		// The template should exclude comments when mailing locally
		$this->factory->comment->create( array( 'comment_post_ID' => $object->id() ) );

		$object->subscribe( $subscriber->ID );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyWelcome' ) );

		Prompt_Subscription_Mailing::send_subscription_notification( $subscriber->ID, $object );

		Prompt_Core::$options->reset();
	}

	function verifyWelcome() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals(
			$this->mail_data->subscriber->user_email,
			$values[0]['to_address'],
			'Expected email to the agreement recipient only.'
		);

		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertContains(
			strip_tags( $this->mail_data->object->subscription_object_label() ),
			$template['subject'],
			'Expected the object label in the email subject.'
		);
		$this->assertNotContains(
			Prompt_Core::$options->get( 'subscribed_introduction' ),
			$template['html_content'],
			'Expected NO custom subscribed introduction content in post subscribed email.'
		);
		$this->assertNotContains(
			'previous-comments',
			$template['html_content'],
			'Expected no previous comments in the welcome email.'
		);
		$this->assertNotContains(
			'reply-prompt',
			$template['html_content'],
			'Expected no reply prompt in the welcome email.'
		);
		$this->assertNotContains(
			'mailto:',
			$template['footnote_html'],
			'Expected no mailto link in the footnote.'
		);
		$this->assertEquals(
			'donotreply@gopostmatic.com',
			$template['reply_to'],
			'Expected do-not-reply reply-to address.'
		);
	}

	function testWelcomeInvalidEmail() {

		$object = new Prompt_Site_Comments();
		$subscriber = $this->factory->user->create_and_get( array( 'user_email' => '23kjk3' ) );

		$this->mailer_expects = $this->never();

		$this->expectException( 'PHPUnit_Framework_Error' );

		Prompt_Subscription_Mailing::send_subscription_notification( $subscriber->ID, $object );
	}

}
