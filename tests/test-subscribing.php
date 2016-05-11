<?php

class SubscriptionHandlingTest extends WP_UnitTestCase {

	function testCreateSubscribableSite() {
		$site = Prompt_Subscribing::make_subscribable();

		$this->assertInstanceOf( 'Prompt_Site', $site );
	}

	function testCreateSubscribableSiteFromSlug() {
		$site = Prompt_Subscribing::make_subscribable( 'site/' . get_current_blog_id() );

		$this->assertInstanceOf( 'Prompt_Site', $site );
	}

	function testCreateSubscribablePost() {
		$post = $this->factory->post->create_and_get();

		$prompt_post = Prompt_Subscribing::make_subscribable( $post );

		$this->assertInstanceOf( 'Prompt_Post', $prompt_post );
		$this->assertEquals( $post->ID, $prompt_post->id() );
	}

	function testCreateSubscribablePostFromSlug() {
		$post = $this->factory->post->create_and_get();

		$prompt_post = Prompt_Subscribing::make_subscribable( 'post/' . $post->ID );

		$this->assertInstanceOf( 'Prompt_Post', $prompt_post );
		$this->assertEquals( $post->ID, $prompt_post->id() );
	}

	function testCreateSubscribableUser() {
		$user = $this->factory->user->create_and_get();

		$prompt_user = Prompt_Subscribing::make_subscribable( $user );

		$this->assertInstanceOf( 'Prompt_User', $prompt_user );
		$this->assertEquals( $user->ID, $prompt_user->id() );
	}
	
	function testCreateSubscribableUserFromSlug() {
		$user = $this->factory->user->create_and_get();

		$prompt_user = Prompt_Subscribing::make_subscribable( 'user/' . $user->ID );

		$this->assertInstanceOf( 'Prompt_User', $prompt_user );
		$this->assertEquals( $user->ID, $prompt_user->id() );
	}

	function testGetSubscribableClasses() {
		$classes = Prompt_Subscribing::get_subscribable_classes();

		$this->assertCount( 3, $classes );
		$this->assertContains( 'Prompt_Site', $classes );
		$this->assertContains( 'Prompt_Post', $classes );
		$this->assertContains( 'Prompt_User', $classes );
	}

	function testGetSubscribableClassesFilter() {
		$result = array( 'test' );

		$mock_filter = $this->getMock( 'Foo', array( 'filter' ) );
		$mock_filter->expects( $this->once() )
			->method( 'filter' )
			->willReturn( $result );

		add_filter( 'prompt/subscribing/get_subscribable_classes', array( $mock_filter, 'filter' ) );

		$classes = Prompt_Subscribing::get_subscribable_classes();

		$this->assertEquals( $result, $classes, 'Exptected the filter return value.' );
	}

	function testGetSubscribableSlug() {

		$this->assertEquals( 'site/' . get_current_blog_id(), Prompt_Subscribing::get_subscribable_slug( new Prompt_Site() ) );

		$prompt_user = new Prompt_User( $this->factory->user->create() );
		$this->assertEquals( 'user/' . $prompt_user->id(), Prompt_Subscribing::get_subscribable_slug( $prompt_user ) );
		
		$prompt_post = new Prompt_Post( $this->factory->post->create() );
		$this->assertEquals( 'post/' . $prompt_post->id(), Prompt_Subscribing::get_subscribable_slug( $prompt_post ) );
	}
}
