<?php

class CommentTest extends Prompt_UnitTestCase {

	/** @var object|WP_Comment */
	protected $_wp_comment;
	/** @var Prompt_Comment */
	protected $_prompt_comment;

	function setUp() {
		parent::setUp();
		$this->_wp_comment = $this->factory->comment->create_and_get();
		$this->_prompt_comment = new Prompt_Comment( $this->_wp_comment->comment_ID );
	}

	function testObjectConstruction() {
		$prompt_comment = new Prompt_Comment( $this->_wp_comment );

		$this->assertEquals( 
			$this->_wp_comment->comment_ID, 
			$prompt_comment->get_wp_comment()->comment_ID, 
			'Expected equal comment IDs.' 
		);
	}

	function testGetId() {
		$this->assertEquals( $this->_wp_comment->comment_ID, $this->_prompt_comment->id() );
	}

	function testGetWpComment() {
		$this->assertEquals( 
			$this->_wp_comment->comment_ID, 
			$this->_prompt_comment->get_wp_comment()->comment_ID, 
			'Expected equal comment IDs.' 
		);
	}

	function testRecipientIds() {
		
		$this->assertEmpty( $this->_prompt_comment->get_recipient_ids(), 'Expected no recipient IDs initially.' );
		
		$ids = array( 2, 3, 5 );
		
		$this->assertEquals( 
			$ids, 
			$this->_prompt_comment->set_recipient_ids( $ids )->get_recipient_ids(),
			'Expected the IDs that were set.' 
		);
	}
	
	function testSentIds() {
		
		$this->assertEmpty( $this->_prompt_comment->get_sent_subscriber_ids(), 'Expected no sent IDs initially.' );
		
		$ids = array( 2, 3, 5 );
		
		$this->assertEquals( 
			$ids, 
			$this->_prompt_comment->set_sent_subscriber_ids( $ids )->get_sent_subscriber_ids(),
			'Expected the IDs that were set.' 
		);
	}
	
	function testBatchIds() {
		
		$this->assertEmpty( $this->_prompt_comment->get_sent_batch_ids(), 'Expected no sent IDs initially.' );
		
		$this->_prompt_comment->add_sent_batch_id( 3 );
		
		$this->assertEquals( 
			array( 3 ), 
			$this->_prompt_comment->get_sent_batch_ids(),
			'Expected the IDs that were set.' 
		);
	}
		
	function testSubscriptionRequested() {
		
		$this->assertFalse( $this->_prompt_comment->get_subscription_requested(), 'Expected no subscription requested.' );
		
		$this->assertTrue(
			$this->_prompt_comment->set_subscription_requested()->get_subscription_requested(),
			'Expected subscription request to be set.'
		);
	}
	
	function testGetAuthorUser() {
		
		$this->assertFalse( 
			$this->_prompt_comment->get_author_user(),
			'Expected no author user.'
		);
		
		$user = $this->factory->user->create_and_get();
		
		$id_comment = $this->factory->comment->create_and_get( array( 'user_id' => $user->ID ) );
		
		$id_prompt_comment = new Prompt_Comment( $id_comment );
		
		$this->assertEquals( $user, $id_prompt_comment->get_author_user() );
		
		$email_comment = $this->factory->comment->create_and_get( array( 'comment_author_email' => $user->user_email ) );
		
		$email_prompt_comment = new Prompt_Comment( $email_comment );
		
		$this->assertEquals( $user, $email_prompt_comment->get_author_user() );
	}
	
	function testGetAuthorName() {
		
		$this->assertEquals( $this->_wp_comment->comment_author, $this->_prompt_comment->get_author_name() );

		$user = $this->factory->user->create_and_get();

		$id_comment = $this->factory->comment->create_and_get( array( 'user_id' => $user->ID ) );
		
		$id_prompt_comment = new Prompt_Comment( $id_comment );

		$this->assertEquals( $user->display_name, $id_prompt_comment->get_author_name() );
	}
	
	function testHasSubscriberData() {
		
		$this->assertFalse( $this->_prompt_comment->author_can_subscribe(), 'Expected not to find subscriber data.' );
		
		$email_comment = new Prompt_Comment( 
			$this->factory->comment->create( array( 'comment_author_email' => 'test@example.com' ) )
		);

		$this->assertTrue( $email_comment->author_can_subscribe(), 'Expected to find subscriber data.' );

		$user_comment = new Prompt_Comment( $this->factory->comment->create( array( 'user_id' => '5' ) ) );

		$this->assertTrue( $user_comment->author_can_subscribe(), 'Expected to find subscriber data.' );
	}
}

