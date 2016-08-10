<?php

class HandlebarsTest extends PHPUnit_Framework_TestCase {

	public function test_autoload() {
		new Prompt_Handlebars();
		$this->assertTrue( class_exists( 'Handlebars_Engine' ), 'Expected handlebars engine class to be autoloaded.' );
	}

	public function test_escape_expressions() {
		$handlebars = new Prompt_Handlebars();
		$content = 'a {{mustache}} {{{too}}} {far}';
		$escaped_content = $handlebars->escape_expressions( $content );

		$this->assertEquals(
			'a \\{{mustache}} \\{{{too}}} {far}',
			$escaped_content,
			'Expected double and triple stash expressions to be escaped with a backslash.'
		);
	}
}
