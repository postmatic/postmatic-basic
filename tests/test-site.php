<?php

class SiteTest extends WP_UnitTestCase {

	/** @var Prompt_Site */
	protected $_prompt_site;
	/** @type string */
	protected static $option_key = 'prompt_subscribed_user_ids';

	function setUp() {
		parent::setUp();
		$this->_prompt_site = new Prompt_Site;
	}

	function testGetId() {
		$this->assertEquals( get_current_blog_id(), $this->_prompt_site->id() );
	}

	function testIsSubscribed() {
		$user_id = $this->factory->user->create();
		update_option( self::$option_key, array( $user_id ) );
		$this->assertTrue(
			$this->_prompt_site->is_subscribed( $user_id ),
			'Expected user to be subscribed.'
		);
	}

	function testSubscribe() {
		$user1_id = $this->factory->user->create();
		$user2_id = $this->factory->user->create();

		delete_option( self::$option_key );

		$ids[] = $user1_id;
		$this->_prompt_site->subscribe( $user1_id );

		$check_ids = get_option( self::$option_key );
		$this->assertEquals( $ids, $check_ids, 'Expected first subscribed user ID to be added to option data.' );

		$ids[] = $user2_id;
		$this->_prompt_site->subscribe( $user2_id );

		$check_ids = get_option( self::$option_key );
		$this->assertEquals( $ids, $check_ids, 'Expected second subscribed user ID to be added to option data.' );
	}

	function testUnsubscribe() {
		$user1_id = $this->factory->user->create();
		$user2_id = $this->factory->user->create();

		$ids = array( $user1_id, $user2_id );
		update_option( self::$option_key, $ids );

		$this->_prompt_site->unsubscribe( $user2_id );

		array_pop( $ids );
		$check_ids = get_option( self::$option_key );

		$this->assertEquals( $ids, $check_ids, 'Expected second subscribed user ID to be removed from option data.' );

		$this->_prompt_site->unsubscribe( $user1_id );

		$check_ids = get_option( self::$option_key );

		$this->assertEmpty( $check_ids, 'Expected no subscriber IDs left in option data.' );
	}

	function testSubscriptionUrl() {
		$this->assertEquals(
			home_url(),
			$this->_prompt_site->subscription_url(),
			'Expected the home URL as subscription URL.'
		);
	}

	function testSubscriptionObjectLabel() {
		$this->assertContains(
			get_option( 'blogname' ),
			$this->_prompt_site->subscription_object_label(),
			'Exptected to see the blog name in the object label.'
		);
	}

	function testSubscriptionDescription() {
		$this->assertContains(
			get_option( 'blogname' ),
			$this->_prompt_site->subscription_description(),
			'Exptected to see the blog name in the description.'
		);
	}

	function testSubscribePhrase() {
		$this->assertNotEmpty( $this->_prompt_site->subscribe_phrase(), 'Expected a nonempty subscribe phrase.' );
	}

	function testMatchesSubscribePhrase() {
		$this->assertTrue(
			$this->_prompt_site->matches_subscribe_phrase( $this->_prompt_site->subscribe_phrase() ),
			'Expected the subscribe phrase to match itself.'
		);
		$this->assertFalse(
			$this->_prompt_site->matches_subscribe_phrase( 'foo' ),
			'Expected the subscribe phrase NOT to match foo.'
		);
	}

	function testSelectReplyPrompt() {
		$this->assertContains( '{{reply_to}}', $this->_prompt_site->select_reply_prompt() );
		$this->assertNotContains( '{{reply_to}}', $this->_prompt_site->select_reply_prompt( Prompt_Enum_Content_Types::TEXT ) );
	}

	function testSubscribePrompt() {
		$this->assertNotEmpty( $this->_prompt_site->subscribe_prompt() );
		$this->assertNotContains( '{{reply_to}}', $this->_prompt_site->select_reply_prompt( Prompt_Enum_Content_Types::TEXT ) );
	}

	function testNullSubscribeFailure() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$this->_prompt_site->subscribe( null );
	}

	function testZeroSubscribeFailure() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$this->_prompt_site->subscribe( 0 );
	}

	function testAllSubscriberIds() {
		$user_ids = $this->factory->user->create_many( 4 );

		$this->_prompt_site->subscribe( $user_ids[0] );
		$this->_prompt_site->subscribe( $user_ids[1] );
		$this->_prompt_site->subscribe( $user_ids[2] );

		$this->assertEmpty(
			array_diff( array_slice( $user_ids, 0, 3 ), Prompt_Site::all_subscriber_ids() ),
			'Expected first three users as site subscribers.'
		);
	}
}

