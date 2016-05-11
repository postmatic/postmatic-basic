<?php

class SubscribeMatcherTest extends WP_UnitTestCase {

	function testTarget() {
		$this->assertEquals( 'subscribe', Prompt_Subscribe_Matcher::target() );
	}

	/**
	 * @dataProvider subscribeCommandProvider
	 */
	function testMatches( $text ) {
		$matcher = new Prompt_Subscribe_Matcher( $text );
		$this->assertTrue( $matcher->matches(), 'Expected a match for ' . $text );
	}

	function subscribeCommandProvider() {
		$unicode_nonbreaking_space = chr(0xc2) . chr( 0xa0 );
		return array(
			array( 'subscribe' ),
			array( $unicode_nonbreaking_space . 'subscribe' ),
			array( '*subscribe*' ),
			array( 'Subscribe' ),
			array( 'usbscribe' ),
			array( 'USBSCRIBE' ),
			array( 'suscribe' ),
			array( 'susribe' ),
			array( 'susrib' ),
		);
	}

	function testNoMatch() {
		$text = 'unsubscribe';
		$matcher = new Prompt_Subscribe_Matcher( $text );
		$this->assertFalse( $matcher->matches(), 'Expected no match for ' . $text );
	}
}