<?php

class SignerTest extends PHPUnit_Framework_TestCase {

	function test_email_signature() {

		$data = array(
			'email' => 'test.this+address@example.tld'
		);

		$key = 'foo';
		$value = 'bar';
		$hasher_mock = $this->getMock( 'Prompt_Hasher', array( 'hash' ), array( $key ) );
		$hasher_mock->expects( $this->exactly( 2 ) )
			->method( 'hash' )
			->willReturn( $value );

		$signer = new Prompt_Signer( $hasher_mock );

		$base_url = 'http://example.com?a=b';

		$url = $signer->sign_url( $base_url, $data );

		$this->assertNotEmpty( $url, 'Expected an URL.' );
		$this->assertContains( $base_url, $url );
		$this->assertContains( urlencode( $data['email'] ), $url );
		$this->assertContains( $value, $url );

	}

	function test_valid_signature() {

		$data = array(
			'email' => 'test@example.com',
			't' => 'token',
			's' => 'signature',
		);

		$key = 'foo';
		$hasher_mock = $this->getMock( 'Prompt_Hasher', array( 'hash' ), array( $key ) );
		$hasher_mock->expects( $this->once() )
			->method( 'hash' )
			->with( $data['t'] . $data['email'] )
			->willReturn( $data['s'] );

		$signer = new Prompt_Signer( $hasher_mock );

		$this->assertTrue( $signer->is_valid( $data ), 'Expected valid signature.' );
	}

	function test_invalid_signature() {

		$data = array(
			'email' => 'test@example.com',
			't' => 'fake',
			's' => 'args',
		);

		$key = 'foo';
		$value = 'bar';
		$hasher_mock = $this->getMock( 'Prompt_Hasher', array( 'hash' ), array( $key ) );
		$hasher_mock->expects( $this->once() )
			->method( 'hash' )
			->willReturn( $value );

		$signer = new Prompt_Signer( $hasher_mock );

		$this->assertFalse( $signer->is_valid( $data ), 'Expected invalid signature.' );
	}
}
