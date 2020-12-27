<?php

class HandlebarsTest extends PHPUnit_Framework_TestCase {

	public function test_autoload() {
		new Prompt_Handlebars();
		$this->assertTrue( class_exists( \Handlebars\Handlebars::class ), 'Expected handlebars engine class to be autoloaded.' );
	}

}
