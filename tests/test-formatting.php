<?php

class FormattingTest extends PHPUnit_Framework_TestCase {

	public function test_escape_handlebars_expressions() {
		$content = 'a {{mustache}} {{{too}}} {far}';
		$escaped_content = Prompt_Formatting::escape_handlebars_expressions( $content );

		$this->assertEquals(
			'a \\{{mustache}} \\{{{too}}} {far}',
			$escaped_content,
			'Expected double and triple stash expressions to be escaped with a backslash.'
		);
	}

}
