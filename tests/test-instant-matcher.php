<?php

class InstantMatcherTest extends WP_UnitTestCase {

	function testTarget() {
		$this->assertEquals( 'instant', Prompt_Instant_Matcher::target() );
	}

	/**
	 * @dataProvider commandProvider
	 */
	function testMatches( $text ) {
		$matcher = new Prompt_Instant_Matcher( $text );
		$this->assertTrue( $matcher->matches(), 'Expected a match for ' . $text );
	}

	function commandProvider() {
		$unicode_nonbreaking_space = chr(0xc2) . chr( 0xa0 );
		return array(
			array( 'aintant' ),
			array( $unicode_nonbreaking_space . 'instant' ),
			array( '*instant*' ),
			array( 'iantatn' ),
			array( 'isntant' ),
			array( 'SINTANT' ),
			array( 'isntantn' ),
		);
	}

}