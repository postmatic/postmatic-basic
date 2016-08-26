<?php

class HandlebarsTest extends PHPUnit_Framework_TestCase {

	public function test_autoload() {
		new Prompt_Handlebars();
		$this->assertTrue( class_exists( 'Handlebars_Engine' ), 'Expected handlebars engine class to be autoloaded.' );
	}

}
