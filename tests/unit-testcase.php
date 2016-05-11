<?php

class Prompt_UnitTestCase extends WP_UnitTestCase {

	protected $remove_outbound_hooks = true;

	function setUp() {
		parent::setUp();

		if ( $this->remove_outbound_hooks ) {
			$this->remove_outbound_hooks();
		}
	}

	function remove_outbound_hooks() {
		remove_action( 'transition_post_status',        array( 'Prompt_Outbound_Handling', 'action_transition_post_status' ) );
		remove_action( 'wp_insert_comment',             array( 'Prompt_Outbound_Handling', 'action_wp_insert_comment' ) );
		remove_action( 'transition_comment_status',     array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ) );
		remove_filter( 'comment_moderation_recipients', array( 'Prompt_Outbound_Handling', 'filter_comment_moderation_recipients' ) );
	}


}