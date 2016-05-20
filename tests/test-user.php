<?php

class UserTest extends WP_UnitTestCase {

	/** @var WP_User */
	protected $_wp_user;
	/** @var Prompt_User */
	protected $_prompt_user;

	function setUp() {
		parent::setUp();
		$this->_wp_user = $this->factory->user->create_and_get();
		$this->_prompt_user = new Prompt_User( $this->_wp_user->ID );
	}

	function testObjectConstruction() {
		$prompt_user = new Prompt_User( $this->_wp_user );

		$this->assertEquals( $this->_wp_user->ID, $prompt_user->get_wp_user()->ID, 'Expected equal user IDs.' );
	}

	function testGetId() {
		$this->assertEquals( $this->_wp_user->ID, $this->_prompt_user->id(), 'Expected equal user IDs.' );
	}

	function testSubscriberProfileOptions() {
		$subscriber = $this->factory->user->create_and_get();
		$this->_prompt_user->subscribe( $subscriber->ID );

		$form = $this->_prompt_user->profile_options();

		$this->assertContains( $subscriber->display_name, $form, 'Expected subscriber to be listed.' );
	}

	function testSiteProfileOptions() {

		$form = $this->_prompt_user->profile_options();

		$this->assertContains( 'prompt_site_subscribed', $form, 'Expected site option.' );
		$this->assertNotContains( 'prompt_site_comments_subscribed', $form, 'Expected site comments option.' );
		$this->assertNotRegExp( '/checked="checked"[^>]*prompt_site_subscribed/', $form, 'Expected unchecked site option.' );

		$this->_prompt_user->update_profile_options( array(
			'prompt_site_subscribed' => array( get_current_blog_id() ),
		) );

		$form = $this->_prompt_user->profile_options();

		$this->assertContains( 'prompt_site_subscribed', $form, 'Expected site option.' );
		$this->assertNotRegExp( '/checked="checked"[^>]*prompt_site_subscribed/', $form, 'Expected unchecked site option.' );

		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->_prompt_user->id() );

		$this->_prompt_user->update_profile_options( array(
			'prompt_site_subscribed' => array( get_current_blog_id() ),
		) );

		$form = $this->_prompt_user->profile_options();

		$this->assertRegExp( '/checked="checked"[^>]*prompt_site_subscribed/', $form, 'Expected checked site option.' );

		wp_set_current_user( $current_user_id );
		Prompt_Core::$options->reset();
	}

	function testAdminProfileOptions() {
		$prompt_admin = new Prompt_User( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$form = $prompt_admin->profile_options();

		$this->assertContains( 'prompt_site_comments_subscribed', $form, 'Expected site comments option.' );
		$this->assertNotRegExp(
			'/checked="checked"[^>]*prompt_site_comments_subscribed/',
			$form,
			'Expected unchecked site comments option.'
		);

		$prompt_admin->update_profile_options(  array( 'prompt_site_comments_subscribed' => array( get_current_blog_id() ) ) );

		$form = $prompt_admin->profile_options();

		$this->assertContains( 'prompt_site_comments_subscribed', $form, 'Expected site comments option.' );
		$this->assertNotRegExp(
			'/checked="checked"[^>]*prompt_site_comments_subscribed/',
			$form,
			'Expected unchecked site comments option.'
		);

		$current_user_id = get_current_user_id();
		wp_set_current_user( $prompt_admin->id() );

		$prompt_admin->update_profile_options(  array( 'prompt_site_comments_subscribed' => array( get_current_blog_id() ) ) );

		$form = $prompt_admin->profile_options();

		$this->assertContains( 'prompt_site_comments_subscribed', $form, 'Expected site comments option.' );
		$this->assertRegExp(
			'/checked="checked"[^>]*prompt_site_comments_subscribed/',
			$form,
			'Expected checked site comments option.'
		);

		wp_set_current_user( $current_user_id );
	}

	function testAuthorProfileOptions() {
		$prompt_author = new Prompt_User( $this->factory->user->create() );
		$prompt_author->subscribe( $this->_prompt_user->id() );

		$form = $this->_prompt_user->profile_options();

		$this->assertContains( 'prompt-author-subscriptions',  $form );
		$this->assertContains( $prompt_author->get_wp_user()->display_name, $form );
	}

	function testSubscriptionUrl() {
		$this->assertEquals(
			get_author_posts_url( $this->_wp_user->ID ),
			$this->_prompt_user->subscription_url(),
			'Expected the author posts URL as subscription URL.'
		);
	}

	function testSubscriptionObjectLabel() {
		$this->assertContains(
			$this->_wp_user->display_name,
			$this->_prompt_user->subscription_object_label(),
			'Expected to see the user display name in the object label.'
		);
		$this->assertContains(
			$this->_wp_user->display_name,
			$this->_prompt_user->subscription_object_label( Prompt_Enum_Content_Types::TEXT ),
			'Expected to see the user display name in the object label.'
		);
	}

	function testSubscriptionDescription() {
		$this->assertContains(
			$this->_wp_user->display_name,
			$this->_prompt_user->subscription_description(),
			'Exptected to see the post title in the description.'
		);
		$this->assertContains(
			$this->_wp_user->display_name,
			$this->_prompt_user->subscription_description( Prompt_Enum_Content_Types::TEXT ),
			'Exptected to see the post title in the description.'
		);
	}

	function testSubscribePhrase() {
		$this->assertNotEmpty( $this->_prompt_user->subscribe_phrase(), 'Expected a nonempty subscribe phrase.' );
	}

	function testMatchesSubscribePhrase() {
		$this->assertTrue(
			$this->_prompt_user->matches_subscribe_phrase( $this->_prompt_user->subscribe_phrase() ),
			'Expected the subscribe phrase to match itself.'
		);
		$this->assertFalse(
			$this->_prompt_user->matches_subscribe_phrase( 'foo' ),
			'Expected the subscribe phrase NOT to match foo.'
		);
	}

	function testSelectReplyPrompt() {
		$this->assertContains( '{{reply_to}}', $this->_prompt_user->select_reply_prompt() );
		$this->assertNotContains( '{{reply_to}}', $this->_prompt_user->select_reply_prompt( Prompt_Enum_Content_Types::TEXT ) );
	}

	function testDeleteUser() {
		$prompt_post1 = new Prompt_Post( $this->factory->post->create() );
		$prompt_post2 = new Prompt_Post( $this->factory->post->create() );
		$prompt_user1 = new Prompt_Post( $this->factory->user->create() );
		$prompt_user2 = new Prompt_Post( $this->factory->user->create() );

		$site = new Prompt_Site();

		$prompt_post1->subscribe( $prompt_user1->id() );
		$prompt_post1->subscribe( $prompt_user2->id() );
		$prompt_post2->subscribe( $prompt_user1->id() );
		$prompt_post2->subscribe( $prompt_user2->id() );
		$site->subscribe( $prompt_user1->id() );
		$site->subscribe( $prompt_user2->id() );

		wp_delete_user( $prompt_user1->id() );

		$this->assertFalse( $prompt_post1->is_subscribed( $prompt_user1->id() ), 'Expected user to be unsubscribed.' );
		$this->assertFalse( $prompt_post2->is_subscribed( $prompt_user1->id() ), 'Expected user to be unsubscribed.' );
		$this->assertFalse( $site->is_subscribed( $prompt_user1->id() ), 'Expected user to be unsubscribed.' );
		$this->assertTrue( $prompt_post1->is_subscribed( $prompt_user2->id() ), 'Expected user to be subscribed.' );
		$this->assertTrue( $prompt_post2->is_subscribed( $prompt_user2->id() ), 'Expected user to be subscribed.' );
		$this->assertTrue( $site->is_subscribed( $prompt_user2->id() ), 'Expected user to be subscribed.' );
	}

	function testAllSubscriberIds() {
		$user_ids = $this->factory->user->create_many( 8 );

		$prompt_author1 = new Prompt_User( $user_ids[2] );
		$prompt_author2 = new Prompt_User( $user_ids[7] );

		$prompt_author1->subscribe( $user_ids[0] );
		$prompt_author1->subscribe( $user_ids[1] );

		$prompt_author2->subscribe( $user_ids[1] );
		$prompt_author2->subscribe( $user_ids[2] );

		$this->assertEmpty(
			array_diff( array_slice( $user_ids, 0, 3 ), Prompt_User::all_subscriber_ids() ),
			'Expected first three users as author subscribers.'
		);
	}

	function testSetSubscriberOrigin() {
		$origin_data = array(
			'timestamp' => time(),
			'source_label' => 'Test Label',
			'source_url' => 'http://test.postmatic.tld',
			'agreement' => 'test agreement text',
		);

		$this->assertNull( $this->_prompt_user->get_subscriber_origin(), 'Expected null origin to start with.' );

		$this->_prompt_user->set_subscriber_origin( new Prompt_Subscriber_Origin( $origin_data ) );

		$check_user = new Prompt_User( $this->_prompt_user->id() );

		$origin = $check_user->get_subscriber_origin();

		$this->assertEquals( $origin_data['timestamp'], $origin->get_timestamp(), 'Expected original timestamp.' );
		$this->assertEquals( $origin_data['source_label'], $origin->get_source_label(), 'Expected original source label.' );
		$this->assertEquals( $origin_data['source_url'], $origin->get_source_url(), 'Expected original source url.' );
		$this->assertEquals( $origin_data['agreement'], $origin->get_agreement(), 'Expected original agreement.' );
	}

	function testProfileSubscriberOrigin() {

		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->_prompt_user->id() );
		
		$this->_prompt_user->update_profile_options( array(
			'prompt_site_subscribed' => array( get_current_blog_id() ),
		) );

		$origin = $this->_prompt_user->get_subscriber_origin();

		$this->assertNotNull( $origin, 'Expected a subscriber origin.' );
		$this->assertLessThanOrEqual( time(), $origin->get_timestamp(), 'Expected current timestamp.' );
		$this->assertEquals( 'Existing User', $origin->get_source_label(), 'Expected original source label.' );
		$this->assertEquals( scbUtil::get_current_url(), $origin->get_source_url(), 'Expected original source url.' );
		$this->assertEquals( 'checkbox', $origin->get_agreement(), 'Expected original agreement.' );
		
		wp_set_current_user( $current_user_id );
	}
	
	function testDeleteAllSubscriptionsReturn() {
		
		$prompt_user = new Prompt_User( $this->factory->user->create_and_get() );
		
		$site = new Prompt_Site();
		$site->subscribe( $prompt_user->id() );
		
		$author = new Prompt_User( $this->factory->user->create_and_get() );
		$author->subscribe( $prompt_user->id() );
		
		$unsub_lists = $prompt_user->delete_all_subscriptions();
		
		$this->assertFalse( $site->is_subscribed( $prompt_user->id() ), 'Expected user to be unsubscribed from site.' );
		$this->assertFalse( 
			$author->is_subscribed( $prompt_user->id() ), 
			'Expected user to be unsubscribed from author.' 
		);
		
		$unsub_classes = array_map( 'get_class', $unsub_lists );
		$this->assertContains( 'Prompt_Site', $unsub_classes );
		$this->assertContains( 'Prompt_User', $unsub_classes );
	}
}

