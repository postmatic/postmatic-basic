<?php

class HandlebarsEscapePostRenderingModifierTest extends WP_UnitTestCase {

	function test_filter_registration() {

		$modifier = new Prompt_Handlebars_Escape_Post_Rendering_Modifier();

		$this->assertFalse(
			has_filter( 'the_content', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected no content filter prior to setup.'
		);
		$this->assertFalse(
			has_filter( 'the_excerpt', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected no excerpt filter prior to setup.'
		);
		$this->assertFalse(
			has_filter( 'the_title', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected no title filter prior to setup.'
		);

		$modifier->setup();

		$this->assertEquals(
			10,
			has_filter( 'the_content', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected the content filter after setup.'
		);
		$this->assertEquals(
			10,
			has_filter( 'the_excerpt', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected the excerpt filter after setup.'
		);
		$this->assertEquals(
			10,
			has_filter( 'the_title', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected the titel filter after setup.'
		);

		$modifier->reset();

		$this->assertFalse(
			has_filter( 'the_content', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected no content filter after reset.'
		);
		$this->assertFalse(
			has_filter( 'the_excerpt', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected no excerpt filter after reset.'
		);
		$this->assertFalse(
			has_filter( 'the_title', array( 'Prompt_Formatting', 'escape_handlebars_expressions' ) ),
			'Expected no title filter after reset.'
		);
	}

}
