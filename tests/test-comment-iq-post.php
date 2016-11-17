<?php

class CommentIQPostTest extends WP_UnitTestCase {

	public function test_article_id_default() {
		$post = $this->factory->post->create_and_get();

		$this->assertNull( $post->get_article_id(), 'Expected null article ID.' );
	}

}