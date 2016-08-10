<?php

class HandlebarsEscapePostRenderingModifierTest extends WP_UnitTestCase {

	function test_filter_execution() {

		$handlebars_mock = $this->getMock( 'Prompt_Handlebars' );
		$handlebars_mock->expects( $this->once() )
			->method( 'escape_expressions' )
			->willReturn( 'PASS' );

		$modifier = new Prompt_Handlebars_Escape_Post_Rendering_Modifier( $handlebars_mock );

		$modifier->setup();

		$this->assertContains( 'PASS', apply_filters( 'the_content', 'TEST' ), 'Expected mock content.' );

		$modifier->reset();

	}

}
