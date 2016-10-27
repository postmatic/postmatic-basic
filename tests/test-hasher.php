<?php

class HasherTest extends PHPUnit_Framework_TestCase {

	function test_known_key_value() {
		$hasher = new Prompt_Hasher( 'foo' );
		$this->assertEquals(
			'3a5c1437614283a4',
			$hasher->hash( 'test' ),
			'Expected pre-computed hash value for a known key and value.'
		);
	}
}