<?php

class OptinsTest extends WP_UnitTestCase {

	function testOptions() {
		$options = Prompt_Optins::optins_options();
		$this->assertNotEmpty( $options, 'Expected non-empty options.' );
		$this->assertEquals( 'all', $options['optins_inpost_ids'] );
	}

	function testNoPostOptinOnPages() {
		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		query_posts( array( 'page_id' => $page_id ) );

		Prompt_Optins::maybe_inpost();

		$this->assertFalse(
			has_action( 'the_content', array( 'Prompt_Optins', 'content_filter' ) ),
			'Expected no optin content filter for a page.'
		);

		wp_reset_query();
	}

	function testNoPostOptinOnCustomType() {
		register_post_type( 'foo_pt' );

		$post_id = $this->factory->post->create( array( 'post_type' => 'foo_pt' ) );

		query_posts( array( 'p' => $post_id, 'post_type' => 'foo_pt' ) );

		Prompt_Optins::maybe_inpost();

		$this->assertFalse(
			has_action( 'the_content', array( 'Prompt_Optins', 'content_filter' ) ),
			'Expected no optin content filter for a custom post type.'
		);

		wp_reset_query();
	}

}
