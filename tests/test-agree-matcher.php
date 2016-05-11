<?php

class AgreeMatcherTest extends WP_UnitTestCase {

	function testTarget() {
		$this->assertEquals( 'agree', Prompt_Agree_Matcher::target() );
	}

	/**
	 * @dataProvider commandProvider
	 */
	function testMatches( $text ) {
		$matcher = new Prompt_Agree_Matcher( $text );
		$this->assertTrue( $matcher->matches(), 'Expected a match for ' . $text );
	}

	function commandProvider() {
		$unicode_nonbreaking_space = chr(0xc2) . chr( 0xa0 );
		return array(
			array( 'agree' ),
			array( $unicode_nonbreaking_space . 'agree' ),
			array( 'Agree' ),
			array( 'agreed' ),
			array( 'I AGREE' ),
			array( 'ageree' ),
			array( 'agere' ),
		);
	}

	/**
	 * @dataProvider notAgreedTextProvider
	 */
	function testNotMatches( $text ) {
		$matcher = new Prompt_Agree_Matcher( $text );
		$this->assertFalse( $matcher->matches(), 'Expected NO match for ' . $text );
	}

	function notAgreedTextProvider() {
		return array(
			array( '' ),
			array( 'no no no' ),
			array( 'I DON\'T AGREE.' ),
			array( 'I will not agree to such nonsense.' ),
			array( 'Piss off.' ),
		);
	}

}