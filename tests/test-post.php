<?php

class PostTest extends Prompt_UnitTestCase {

	/** @var WP_Post */
	protected $_wp_post;
	/** @var Prompt_Post */
	protected $_prompt_post;

	function setUp() {
		parent::setUp();
		$this->_wp_post = $this->factory->post->create_and_get();
		$this->_prompt_post = new Prompt_Post( $this->_wp_post->ID );
	}

	function testObjectConstruction() {
		$prompt_post = new Prompt_Post( $this->_wp_post );

		$this->assertEquals( $this->_wp_post->ID, $prompt_post->get_wp_post()->ID, 'Expected equal post IDs.' );
	}

	function testGetId() {
		$this->assertEquals( $this->_wp_post->ID, $this->_prompt_post->id() );
	}

	function testGetWpPost() {
		$this->assertEquals( $this->_wp_post->ID, $this->_prompt_post->get_wp_post()->ID, 'Expected equal post IDs.' );
	}

	function testIsSubscribed() {
		update_post_meta( $this->_wp_post->ID, Prompt_Post::SUBSCRIBED_META_KEY, array( $this->_wp_post->post_author ) );
		$this->assertTrue(
			$this->_prompt_post->is_subscribed( $this->_wp_post->post_author ),
			'Expected author to be subscribed.'
		);
	}

	function testSubscribe() {
		$user1_id = $this->factory->user->create();
		$user2_id = $this->factory->user->create();

		$ids = get_post_meta( $this->_wp_post->ID, Prompt_Post::SUBSCRIBED_META_KEY, true );

		$ids[] = $user1_id;
		$this->_prompt_post->subscribe( $user1_id );

		$check_ids = get_post_meta( $this->_wp_post->ID, Prompt_Post::SUBSCRIBED_META_KEY, true );
		$this->assertEquals( $ids, $check_ids, 'Expected first subscribed user ID to be added to metadata.' );

		$ids[] = $user2_id;
		$this->_prompt_post->subscribe( $user2_id );

		$check_ids = get_post_meta( $this->_wp_post->ID, Prompt_Post::SUBSCRIBED_META_KEY, true );
		$this->assertEquals( $ids, $check_ids, 'Expected second subscribed user ID to be added to metadata.' );
	}

	function testUnsubscribe() {
		$user1_id = $this->factory->user->create();
		$user2_id = $this->factory->user->create();

		$ids = array( $user1_id, $user2_id );
		update_post_meta( $this->_wp_post->ID, Prompt_Post::SUBSCRIBED_META_KEY, $ids );

		$this->_prompt_post->unsubscribe( $user2_id );

		array_pop( $ids );
		$check_ids = get_post_meta( $this->_wp_post->ID, Prompt_Post::SUBSCRIBED_META_KEY, true );

		$this->assertEquals( $ids, $check_ids, 'Expected second subscribed user ID to be removed from metadata.' );

		$this->_prompt_post->unsubscribe( $user1_id );

		$check_ids = get_post_meta( $this->_wp_post->ID, Prompt_Post::SUBSCRIBED_META_KEY, true );

		$this->assertEmpty( $check_ids, 'Expected no subscriber IDs left in metadata.' );
	}

	function testSubscriptionUrl() {
		$this->assertEquals(
			get_permalink( $this->_wp_post ),
			$this->_prompt_post->subscription_url(),
			'Expected the post permalink as subscription URL.'
		);
	}

	function testSubscriptionObjectLabel() {
		$this->assertContains(
			$this->_wp_post->post_title,
			$this->_prompt_post->subscription_object_label(),
			'Expected to see the post title in the object label.'
		);
		$this->assertContains(
			$this->_wp_post->post_title,
			$this->_prompt_post->subscription_object_label( Prompt_Enum_Content_Types::TEXT ),
			'Expected to see the post title in the object label.'
		);}

	function testSubscriptionDescription() {
		$this->assertContains(
			$this->_wp_post->post_title,
			$this->_prompt_post->subscription_description(),
			'Expected to see the post title in the description.'
		);
		$this->assertContains(
			$this->_wp_post->post_title,
			$this->_prompt_post->subscription_description( Prompt_Enum_Content_Types::TEXT ),
			'Expected to see the post title in the description.'
		);
	}

	function testSubscribePhrase() {
		$this->assertNotEmpty( $this->_prompt_post->subscribe_phrase(), 'Expected a nonempty subscribe phrase.' );
	}

	function testMatchesSubscribePhrase() {
		$this->assertTrue(
			$this->_prompt_post->matches_subscribe_phrase( $this->_prompt_post->subscribe_phrase() ),
			'Expected the subscribe phrase to match itself.'
		);
		$this->assertFalse(
			$this->_prompt_post->matches_subscribe_phrase( 'foo' ),
			'Expected the subscribe phrase NOT to match foo.'
		);
	}

	function testSelectReplyPrompt() {
		$this->assertContains( '{{reply_to}}', $this->_prompt_post->select_reply_prompt() );
		$this->assertNotContains( '{{reply_to}}', $this->_prompt_post->select_reply_prompt( Prompt_Enum_Content_Types::TEXT ) );
	}

	function testSubscribedObjectIds() {
		$user_ids = $this->factory->user->create_many( 4 );

		$prompt_post1 = new Prompt_Post( $this->factory->post->create() );
		$prompt_post2 = new Prompt_Post( $this->factory->post->create() );
		$prompt_post3 = new Prompt_Post( $this->factory->post->create() );

		$prompt_post1->subscribe( $user_ids[0] );
		$prompt_post1->subscribe( $user_ids[1] );

		$prompt_post2->subscribe( $user_ids[1] );
		$prompt_post2->subscribe( $user_ids[2] );

		$user0_post_ids = Prompt_Post::subscribed_object_ids( $user_ids[0] );

		$this->assertCount( 1, $user0_post_ids, 'Expected 1 subscribed post.' );
		$this->assertContains( $prompt_post1->id(), $user0_post_ids );

		$user1_post_ids = Prompt_Post::subscribed_object_ids( $user_ids[1] );

		$this->assertCount( 2, $user1_post_ids, 'Expected 2 subscribed posts.' );
		$this->assertContains( $prompt_post1->id(), $user1_post_ids );
		$this->assertContains( $prompt_post2->id(), $user1_post_ids );

		$user2_post_ids = Prompt_Post::subscribed_object_ids( $user_ids[2] );

		$this->assertCount( 1, $user2_post_ids, 'Expected 1 subscribed post.' );
		$this->assertContains( $prompt_post2->id(), $user2_post_ids );

		$user3_post_ids = Prompt_Post::subscribed_object_ids( $user_ids[3] );
		$this->assertEmpty( $user3_post_ids, 'Expected no subscribed posts.' );
	}

	function testAllSubscriberIds() {
		$user_ids = $this->factory->user->create_many( 4 );

		$prompt_post1 = new Prompt_Post( $this->factory->post->create() );
		$prompt_post2 = new Prompt_Post( $this->factory->post->create() );
		$prompt_post3 = new Prompt_Post( $this->factory->post->create() );

		$prompt_post1->subscribe( $user_ids[0] );
		$prompt_post1->subscribe( $user_ids[1] );

		$prompt_post2->subscribe( $user_ids[1] );
		$prompt_post2->subscribe( $user_ids[2] );

		$this->assertEmpty(
			array_diff( array_slice( $user_ids, 0, 3 ), Prompt_Post::all_subscriber_ids() ),
			'Expected first three users as post subscribers.'
		);
	}

	function testRecipientIds() {
		$subscriber_ids = $this->factory->user->create_many( 3 );

		$author_id = $this->factory->user->create();

		$post_id = $this->factory->post->create( array( 'post_author' => $author_id, 'post_status' => 'draft' ) );

		$site = new Prompt_Site();
		$author = new Prompt_User( $author_id );
		$post = new Prompt_Post( $post_id );

		$site->subscribe( $subscriber_ids[0] );
		$author->subscribe( $subscriber_ids[1] );
		$post->subscribe( $subscriber_ids[2] );

		$recipient_ids = $post->recipient_ids();

		$this->assertCount( 2, $recipient_ids, 'Expected two post recipients.' );
		$this->assertContains( $subscriber_ids[0], $recipient_ids, 'Expected site subscriber to be a recipient.' );
		$this->assertContains( $subscriber_ids[1], $recipient_ids, 'Expected author subscriber to be a recipient.' );
		$this->assertNotContains( $subscriber_ids[2], $recipient_ids, 'Expected post subscriber NOT to be a recipient.' );

		$site->unsubscribe( $subscriber_ids[0] );

		$recipient_ids = $post->recipient_ids();

		$this->assertCount( 1, $recipient_ids, 'Expected one post recipient.' );
		$this->assertNotContains( $subscriber_ids[0], $recipient_ids, 'Expected site subscriber to be unsubscribed.' );
	}

	function testRepublishRecipientIds() {
		$subscriber_ids = $this->factory->user->create_many( 2 );

		$post = $this->factory->post->create_and_get();

		$site = new Prompt_Site();

		$site->subscribe( $subscriber_ids[0] );

		// Set publish time recipients
		$prompt_post = new Prompt_Post( $post );
		$recipient_ids = $prompt_post->recipient_ids();

		$this->assertCount( 1, $recipient_ids, 'Expected one post recipient.' );

		$site->subscribe( $subscriber_ids[1] );

		// Still should have only recipients at publish time
		$this->assertCount( 1, $recipient_ids, 'Expected one post recipient.' );

		$post->post_status = 'draft';
		wp_update_post( $post );

		// Pre-publish recipients include new subscriber
		$prompt_post = new Prompt_Post( $post );
		$recipient_ids = $prompt_post->recipient_ids();

		$this->assertCount( 2, $recipient_ids, 'Expected two post recipients in draft status.' );

		$post->post_status = 'publish';
		wp_update_post( $post );

		// Post-publish recipients include new subscriber
		$prompt_post = new Prompt_Post( $post );
		$recipient_ids = $prompt_post->recipient_ids();

		$this->assertCount( 2, $recipient_ids, 'Expected two post recipients after republishing.' );
	}

	function testNullSubscribeFailure() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$this->_prompt_post->subscribe( null );
	}

	function testZeroSubscribeFailure() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$this->_prompt_post->subscribe( 0 );
	}

	function testSiteUnsubscribeAfterSave() {

		remove_action( 'transition_post_status', array( 'Prompt_Outbound_Handling', 'action_transition_post_status' ), 10, 3 );

		$user_id = $this->factory->user->create();

		$site = new Prompt_Site();

		$prompt_post = new Prompt_Post( $this->factory->post->create( array( 'post_status' => 'draft' ) ) );

		$site->subscribe( $user_id );

		$this->assertContains( $user_id, $prompt_post->recipient_ids(), 'Expected to find site subscriber.' );

		$site->unsubscribe( $user_id );

		$post = $prompt_post->get_wp_post();

		$post->post_status = 'publish';

		wp_update_post( $post );

		$this->assertNotContains( $user_id, $prompt_post->recipient_ids(), 'Expected NOT to find unsubscribed user.' );

		add_action( 'transition_post_status', array( 'Prompt_Outbound_Handling', 'action_transition_post_status' ), 10, 3 );
	}

	function testBatchStorage() {
		$prompt_post = new Prompt_Post( $this->factory->post->create() );

		$prompt_post->add_outbound_message_batch_ids( 1 );
		$prompt_post->add_outbound_message_batch_ids( 1 );

		$this->assertCount( 1, $prompt_post->outbound_message_batch_ids() );
		$this->assertContains( 1, $prompt_post->outbound_message_batch_ids() );

		$prompt_post->add_outbound_message_batch_ids( array( 2, 3 ) );

		$this->assertCount( 3, $prompt_post->outbound_message_batch_ids() );
		$this->assertContains( 3, $prompt_post->outbound_message_batch_ids() );
	}

	
	function testCustomHTML() {
		$prompt_post = new Prompt_Post( $this->factory->post->create() );
		
		$this->assertEmpty( 
			$prompt_post->get_custom_html(),
			'Expected no custom HTML.'
		);
		
		$custom_html = '<h1>HTML</h1>';
		
		$this->assertSame( 
			$prompt_post, 
			$prompt_post->set_custom_html( $custom_html ), 
			'Expected custom HTML method to return $this.'
		);
		
		$this->assertEquals(
			$custom_html,
			$prompt_post->get_custom_html(),
			'Expected custom HTML to be the set value.'
		);
	}
	
	// Mulling over the necessity of this method
	function donttestUniqueSubscribers() {
		$author_ids = $this->factory->user->create_many( 2 );
		$subscriber_ids = $this->factory->user->create_many( 4 );

		Prompt_Subscription::ensure_subscribed( 'user', $subscriber_ids[0], $author_ids[0] );
		Prompt_Subscription::ensure_subscribed( 'user', $subscriber_ids[1], $author_ids[0] );
		Prompt_Subscription::ensure_subscribed( 'user', $subscriber_ids[1], $author_ids[1] );
		Prompt_Subscription::ensure_subscribed( 'user', $subscriber_ids[2], $author_ids[1] );

		$author_subscribers = Prompt_Subscription::get_unique_subscriber_ids( 'user' );
		$this->assertCount( 3, $author_subscribers, 'Incorrect author subscriber count.' );
		$this->assertContains( $subscriber_ids[0], $author_subscribers );
		$this->assertContains( $subscriber_ids[1], $author_subscribers );
		$this->assertContains( $subscriber_ids[2], $author_subscribers );

		$post_ids = $this->factory->post->create_many( 2 );
		Prompt_Subscription::ensure_subscribed( 'post', $subscriber_ids[0], $post_ids[0] );
		Prompt_Subscription::ensure_subscribed( 'post', $subscriber_ids[1], $post_ids[0] );
		Prompt_Subscription::ensure_subscribed( 'post', $subscriber_ids[1], $post_ids[1] );
		Prompt_Subscription::ensure_subscribed( 'post', $subscriber_ids[2], $post_ids[1] );

		$post_subscribers = Prompt_Subscription::get_unique_subscriber_ids( 'user' );
		$this->assertCount( 3, $post_subscribers, 'Incorrect post subscriber count.' );
		$this->assertContains( $subscriber_ids[0], $post_subscribers );
		$this->assertContains( $subscriber_ids[1], $post_subscribers );
		$this->assertContains( $subscriber_ids[2], $post_subscribers );
	}

}

