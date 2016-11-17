<?php

class CommentIQSyncTest extends PHPUnit_Framework_TestCase {

	function test_post_sync() {

		$client_mock = $this->getMockBuilder( 'Postmatic/CommentIQ/API/Client' )
			->disableOriginalConstructor()
			->getMock()
			->expects( $this->once() )
			->method( 'add_article' )
			->with( 'TEST ARTICLE' )
			->willReturn( 13 );

		$post_mock = $this->getMockBuilder( 'Prompt_Post' )
			->disableOriginalConstructor()
			->getMock()
			->expects( $this->once() )
			->method( '');

		$sync = new Prompt_Comment_IQ_Sync( $client_mock, $post_mock );


	}
}