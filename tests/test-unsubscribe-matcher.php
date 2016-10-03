<?php

class UnsubscribeMatcherTest extends WP_UnitTestCase {

	function testTarget() {
		$this->assertEquals( 'unsubscribe', Prompt_Unsubscribe_Matcher::target() );
	}

	/**
	 * @dataProvider unsubscribeCommandProvider
	 */
	function testMatches( $text ) {
		$matcher = new Prompt_Unsubscribe_Matcher( $text );
		$this->assertTrue( $matcher->matches(), 'Expected a match for ' . $text );
	}

	function unsubscribeCommandProvider() {
		$unicode_nonbreaking_space = chr(0xc2) . chr( 0xa0 );
		return array(
			array( 'unsubscribe' ),
			array( '​​ unsubscribe ' ),
			array( '?unsubscribe' ),
			array( $unicode_nonbreaking_space . '​unsubscribe' ),
			array( 'Unsubscribe' ),
			array( 'sunsubscribe' ),
			array( 'SUNSUBSCRIBE' ),
			array( 'unsusbscribe' ),
			array( 'unusbscribe' ),
			array( '?unusbscribe' ),
			array( 'unsusribe' ),
			array( 'unsusrib' ),
			array( 'unsubcribe >' ),
			array( 'unsunscribe' ),
			array( 'Unsubscrbe' ),
			array( 'unsubsribe' ),
		);
	}

}

