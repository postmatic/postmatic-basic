<?php

class UsersHandlingTest extends WP_UnitTestCase {

	protected $subscription_column_name = 'prompt_subscriptions';

	function test_column_adding() {
		$test_columns = array(
			'test_name' => 'Test Column',
			'other_name' => 'Other Column',
		);

		$test_columns = Prompt_Admin_Users_Handling::manage_users_columns( $test_columns );

		$this->assertArrayHasKey( 'test_name', $test_columns, 'Expected existing columns to be retained.' );
		$this->assertArrayHasKey(
			$this->subscription_column_name,
			$test_columns,
			'Expected subscriptions column to be added.'
		);
	}

	function test_user_row() {
		$subscriber_id = $this->factory->user->create();
		$author_id = $this->factory->user->create();

		$prompt_site = new Prompt_Site();
		$prompt_site->subscribe( $subscriber_id );

		$prompt_author = new Prompt_User( $author_id );
		$prompt_author->subscribe( $subscriber_id );

		$column_content = Prompt_Admin_Users_Handling::subscriptions_column(
			'ignore',
			$this->subscription_column_name,
			$subscriber_id
		);

		$this->assertNotContains(
			'ignore',
			$column_content,
			'Expected initial content to be overridden.'
		);

		$this->assertContains(
			'prompt-site-subscription',
			$column_content,
			'Expected site subscription hash in column content.'
		);

		$this->assertContains(
			'prompt-author-subscriptions',
			$column_content,
			'Expected author subscription hash in column content.'
		);

		$this->assertNotContains(
			'prompt-post-subscriptions',
			$column_content,
			'Expected no post subscription hash in column content.'
		);
	}
}