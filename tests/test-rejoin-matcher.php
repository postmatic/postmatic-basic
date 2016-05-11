<?php

class RejoinMatcherTest extends WP_UnitTestCase {

	function testTarget() {
		$this->assertEquals( 'rejoin', Prompt_Rejoin_Matcher::target() );
	}

	/**
	 * @dataProvider rejoinCommandProvider
	 */
	function testMatches( $text ) {
		$matcher = new Prompt_Rejoin_Matcher( $text );
		$this->assertTrue( $matcher->matches(), 'Expected a match for ' . $text );
	}

	function rejoinCommandProvider() {
		$unicode_nonbreaking_space = chr(0xc2) . chr( 0xa0 );
		return array(
			array( 'rejoin' ),
			array( $unicode_nonbreaking_space . 'rejoin' ),
			array( '*rejoin*' ),
			array( 'Rejoin' ),
			array( 'rjeion' ),
			array( 'rejion' ),
			array( 'rejoij' ),
		);
	}

}