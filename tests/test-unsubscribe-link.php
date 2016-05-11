<?php

class UnsubscribeLinkTest extends WP_UnitTestCase {

	function testValidLink() {

		$user = $this->factory->user->create_and_get( array( 'user_email' => 'test.this+address@example.tld' ) );

		$original_link = new Prompt_Unsubscribe_Link( $user );

		$this->assertTrue( $original_link->is_valid(), 'Expected valid original link.' );

		$url = $original_link->url();

		$this->assertNotEmpty( $url, 'Expected an unsubscribe URL.' );

		$url_parts = parse_url( $url );

		$args = wp_parse_args( $url_parts['query'] );

		$check_link = new Prompt_Unsubscribe_Link( $args );

		$this->assertNotEmpty( $check_link->user(), 'Expected a user to be found.' );
		$this->assertEquals( $user->ID, $check_link->user()->ID, 'Expected the original user to be found.' );
	}

	function testInvalidLink() {

		$user = $this->factory->user->create_and_get();

		$args = array(
			'email' => $user->user_email,
			't' => 'fake',
			's' => 'args',
		);

		$link = new Prompt_Unsubscribe_Link( $args );

		$this->assertFalse( $link->is_valid(), 'Expected invalid link.' );
	}
}
