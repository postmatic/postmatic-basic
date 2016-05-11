<?php

class LocalPostRenderingModifierTest extends WP_UnitTestCase {

	function testTheContentOverride() {

		$filter = create_function( '$c', 'return $c . "FAIL";' );
		add_filter( 'the_content', $filter, 50 );

		$modifier = new Prompt_Local_Post_Rendering_Modifier();

		$modifier->setup();

		$this->assertNotContains( 'FAIL', apply_filters( 'the_content', 'TEST' ), 'Expected non-appended content.' );

		$modifier->reset();

		$this->assertContains( 'FAIL', apply_filters( 'the_content', 'TEST' ), 'Expected appended content.' );

		remove_filter( 'the_content', $filter, 50 );
	}

}
