<?php

class PromptRegisterSubscribeCommandTest extends Prompt_MockMailerTestCase {

	protected static $user_data_meta_key = 'prompt_user_data';
	protected static $lists_data_meta_key = 'prompt_lists';
	protected static $comment_type = 'prompt_pre_reg';

	function testSaveSubscriptionData() {
		$post_id = $this->factory->post->create();
		$email = 'test@vern.al';
		$user_data = array(
			'first_name' => 'Test',
			'last_name' => 'User',
			'display_name' => 'Test User',
			'user_url' => 'http://test.tld',
		);

		$command = new Prompt_Register_Subscribe_Command();

		$prompt_post = new Prompt_Post( $post_id );
		$command->save_subscription_data( $prompt_post, $email, $user_data );

		$keys = $command->get_keys();

		$this->assertCount( 1, $keys, 'Expected a key with created comment ID.' );

		$comment = get_comment( $keys[0] );

		$this->assertNotEmpty( $comment, 'Expected a saved comment.' );
		$this->assertEquals( $email, $comment->comment_author_email );
		$this->assertEquals( self::$comment_type, $comment->comment_type );
		$this->assertNotEquals( 1, $comment->comment_approved );

		$this->assertEquals( $user_data, get_comment_meta( $keys[0], self::$user_data_meta_key, true ) );

		$this->assertEquals( $prompt_post, get_comment_meta( $keys[0], self::$lists_data_meta_key, true ) );
	}

	function testExecuteAgreed() {
		$post_id = $this->factory->post->create();
		$this->mail_data->address = 'test@vern.al';
		$this->mail_data->subscriber = null;
		add_action( 'prompt/register_subscribe_command/created_user', array( $this, 'verifyCreatedUser' ) );

		$user_data = array(
			'first_name' => 'Test',
			'last_name' => 'User',
			'display_name' => 'Test User',
		);

		$message = new stdClass();
		$message->message = 'I agree.';

		$comment_id = $this->addTempUserComment( $this->mail_data->address, $post_id, $user_data );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifySubscribedEmail' ) );

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $comment_id ) );
		$command->set_message( $message );
		$command->execute();

		$user = $this->mail_data->subscriber;

		$this->assertNotEmpty( $user, 'Expected to find new user by the created user action.' );
		$this->assertEquals( $user_data['first_name'], $user->first_name, 'Expected user first name to be set.' );
		$this->assertEquals( $user_data['last_name'], $user->last_name, 'Expected user last name to be set.' );
		$this->assertEquals( $user_data['display_name'], $user->display_name, 'Expected user display name to be set.' );

		$prompt_user = new Prompt_User( $user );

		$origin = $prompt_user->get_subscriber_origin();

		$prompt_post = new Prompt_Post( $post_id );

		$this->assertGreaterThan( time() - 1000, $origin->get_timestamp(), 'Expected a recent timestamp in origin data.' );
		$this->assertEquals(
			$prompt_post->subscription_object_label(),
			$origin->get_source_label(),
			'Expected post subscription origin label.'
		);
		$this->assertEquals(
			$prompt_post->subscription_url(),
			$origin->get_source_url(),
			'Expected post URL subscription origin.'
		);
		$this->assertEquals(
			$message,
			$origin->get_agreement(),
			'Expected agreemnet to be origin message.'
		);

		$this->assertTrue( $prompt_post->is_subscribed( $user->ID ), 'Expected new user to be subscribed to post.' );

		remove_action( 'prompt/register_subscribe_command/created_user', array( $this, 'verifyCreatedUser' ) );
	}

	function verifyCreatedUser( $user ) {
		$this->assertEquals(
			$this->mail_data->address, $user->user_email,
			'Expected created user action to match email address.'
		);
		$this->mail_data->subscriber = $user;
	}

	function testExecuteNotAgreed() {
		$post_id = $this->factory->post->create();
		$this->mail_data->address = 'test@vern.al';
		$user_data = array( 'display_name' => 'Test User' );

		$message = new stdClass();
		$message->message = 'I DON\'T AGREE.';
		$message->to = 'reply-address@vern.al';

		$comment_id = $this->addTempUserComment( $this->mail_data->address, $post_id, $user_data );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyNotAgreedEmail' ) );

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $comment_id ) );
		$command->set_message( $message );

		$command->execute();

		$user = get_user_by( 'email', $this->mail_data->address );

		$this->assertEmpty( $user, 'Expected NOT to find new user by email.' );
	}

	function verifyNotAgreedEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->address, $values[0]['to_address'] );
		$this->verifyErrorFreeContent();
	}

	function testExecuteNotAgreedExistingUser() {

		$post_id = $this->factory->post->create();
		$address = 'test@example.com';
		$user_data = array( 'display_name' => 'Test User' );

		$message = new stdClass();
		$message->message = 'I DON\'T AGREE.';
		$message->to = 'reply-address@example.com';

		$comment_id = $this->addTempUserComment( $address, $post_id, $user_data );

		// This can happen if a user responds to other registration emails first
		$user_id = $this->factory->user->create( array( 'user_email' => $address ) );

		$this->mailer_expects = $this->never();

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $comment_id ) );
		$command->set_message( $message );

		$command->execute();
	}

	function testExecuteInstant() {
		$this->mail_data->address = 'instant@example.com';
		$this->mail_data->subscriber = null;
		add_action( 'prompt/register_subscribe_command/created_user', array( $this, 'verifyCreatedUser' ) );

		$user_data = array(
			'first_name' => 'Test',
			'last_name' => 'User',
			'display_name' => 'Test User',
		);

		$message = new stdClass();
		$message->message = 'instant';

		$site = new Prompt_Site();
		$lists = array( $site, new Prompt_Site_Comments() );
		$comment_id = $this->addTempUserComment( $this->mail_data->address, $lists, $user_data );

		$this->mailer_will = $this->returnCallback( array( $this, 'verifySubscribedEmail' ) );

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $comment_id ) );
		$command->set_message( $message );
		$command->execute();

		$user = $this->mail_data->subscriber;

		$this->assertNotEmpty( $user, 'Expected to find new user by the created user action.' );

		$this->assertTrue( $site->is_subscribed( $user->ID ), 'Expected new user to be subscribed to site.' );

		remove_action( 'prompt/register_subscribe_command/created_user', array( $this, 'verifyCreatedUser' ) );
	}

	function testDoubleRegistration() {
		$post1_id = $this->factory->post->create();
		$post2_id = $this->factory->post->create();
		$this->mail_data->address = 'test@vern.al';
		$this->mail_data->address2 = 'Test@Vern.al';
		$user_data = array( 'display_name' => 'Test User' );

		$message = new stdClass();
		$message->message = 'agree';

		$comment1_id = $this->addTempUserComment( $this->mail_data->address, $post1_id, $user_data );
		$comment2_id = $this->addTempUserComment( $this->mail_data->address2, $post2_id, $user_data );

		$this->mailer_expects = $this->exactly( 2 );
		$this->mailer_will = $this->returnValue( true );

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $comment1_id ) );
		$command->set_message( $message );
		$command->execute();

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $comment2_id ) );
		$command->set_message( $message );
		$command->execute();

		$user = get_user_by( 'email', $this->mail_data->address );

		$this->assertNotEmpty( $user, 'Expected to find new user by email.' );
		$this->assertEquals( $user_data['display_name'], $user->display_name, 'Expected user display name to be set.' );

		$prompt_post1 = new Prompt_Post( $post1_id );
		$prompt_post2 = new Prompt_Post( $post2_id );

		$this->assertTrue( $prompt_post1->is_subscribed( $user->ID ), 'Expected new user to be subscribed to post.' );
		$this->assertTrue( $prompt_post2->is_subscribed( $user->ID ), 'Expected new user to be subscribed to post.' );
	}

	function testDoubleAgreement() {
		$post_id = $this->factory->post->create();
		$this->mail_data->address = 'test@vern.al';
		$user_data = array( 'display_name' => 'Test User' );

		$message = new stdClass();
		$message->message = 'agree';

		$this->mailer_will = $this->returnValue( true );

		$comment_id = $this->addTempUserComment( $this->mail_data->address, $post_id, $user_data );

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( $comment_id ) );
		$command->set_message( $message );
		$command->execute();
		$command->execute();

		$user = get_user_by( 'email', $this->mail_data->address );

		$this->assertNotEmpty( $user, 'Expected to find new user by email.' );

		$prompt_post = new Prompt_Post( $post_id );

		$this->assertTrue( $prompt_post->is_subscribed( $user->ID ), 'Expected new user to be subscribed to post.' );
	}

	function testNonNumericKeyException() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( 3, 'a' ) );
		$command->execute();
	}

	function testWrongNumberOfKeysException() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );

		$command = new Prompt_Register_Subscribe_Command();
		$command->set_keys( array( 3, 5 ) );
		$command->execute();
	}

	protected function addTempUserComment( $email, $lists, $user_data = array() ) {
		if ( is_int( $lists ) ) {
			$lists = array( new Prompt_Post( $lists ) );
		}
		$comment_id = wp_insert_comment( array(
			'comment_author_email' => $email,
			'comment_type' => self::$comment_type,
			'comment_approved' => 'Prompt',
			'comment_agent' => 'Postmatic/2.0',
		) );
		update_comment_meta( $comment_id, self::$lists_data_meta_key, $lists );
		update_comment_meta( $comment_id, self::$user_data_meta_key, $user_data );

		return $comment_id;
	}

}
