<?php

class UserHandlingTest extends Prompt_MockMailerTestCase {

	function testOptionsForm() {
		$user_id = $this->factory->user->create();

		ob_start();
		Prompt_User_Handling::render_profile_options( $user_id );
		$form_html = ob_get_clean();

		$this->assertNotEmpty( $form_html, 'Expected some profile options.' );
	}

	function testCreateFromEmail() {
		$similar_user_name = 'test';
		$email = $similar_user_name . '@prompt.vern.al';

		$this->factory->user->create( array( 'user_login' => $similar_user_name ) );

		$user_id = Prompt_User_Handling::create_from_email( $email );

		$created_user = get_userdata( $user_id );

		$this->assertNotEquals( $similar_user_name, $created_user->user_login, 'Expected a modified username.' );

		$lookup_user = get_user_by( 'email', $email );
		$this->assertNotEmpty( $lookup_user, 'Expected to find the new user by email address.' );
		$this->assertEquals( $created_user->ID, $lookup_user->ID, 'Expected to find the new user by email address.' );
	}

	function testPasswordEmail() {
		Prompt_Core::$options->set( 'send_login_info', true );

		$this->mail_data->address = 'test@prompt.vern.al';

		$this->mailer_will = $this->returnCallback( array( $this, 'verifyPasswordEmail' ) );

		Prompt_User_Handling::create_from_email( $this->mail_data->address );

		Prompt_Core::$options->reset();
	}

	function verifyPasswordEmail() {
		$values = $this->mailer_payload->get_individual_message_values();
		$this->assertEquals( $this->mail_data->address, $values[0]['to_address'] );

		$template = $this->mailer_payload->get_batch_message_template();
		$this->assertEquals( get_option( 'admin_email' ), $template['from_address'] );
		$this->assertContains( 'Password', $template['html_content'] );
		$this->assertNotContains( 'Notice:', $template['html_content'] );
		$this->assertNotContains( 'Error:', $template['html_content'] );
	}

	function testNoPasswordEmail() {
		Prompt_Core::$options->set( 'send_login_info', false );

		$email = 'test@prompt.vern.al';

		$this->mailer_expects = $this->never();

		Prompt_User_Handling::create_from_email( $email );

		Prompt_Core::$options->reset();
	}

	function testCurrentUserNone() {
		$this->assertNull( Prompt_User_Handling::current_user(), 'Expected no current user.' );
	}

	function testCurrentUserLoggedIn() {
		$user = $this->factory->user->create_and_get();

		wp_set_current_user( $user->ID );

		$check_user = Prompt_User_Handling::current_user();

		$this->assertEquals( $user, $check_user, 'Expected to get the logged in user.' );

		wp_set_current_user( 0 );
	}

	function testCurrentUserCookied() {

		$user = $this->factory->user->create_and_get();

		$_COOKIE['comment_author_'.COOKIEHASH] = $user->display_name;
		$_COOKIE['comment_author_email_'.COOKIEHASH] = $user->user_email;

		$check_user = Prompt_User_Handling::current_user();

		$this->assertEquals( $user, $check_user, 'Expected to get the cookied user.' );
	}
}

