<?php

class EmailCommentRenderingTest extends \WP_UnitTestCase {
		
	public function test_parent_class_rendering() {
		$post_id = $this->factory->post->create();
		$parent_comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $post_id ) );
		$child_comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
			'comment_parent' => $parent_comment->comment_ID,
		) );
		
		Prompt_Email_Comment_Rendering::classify_comments( array( $parent_comment ), 'contextual' );
		
		ob_start();
		Prompt_Email_Comment_Rendering::render( $parent_comment, array(), 0 );
		$parent_content = ob_get_clean();
		
		$this->assertContains( 
			'contextual',
			$parent_content,
			'Expected contextual class in parent content.'
		);
	
		ob_start();
		Prompt_Email_Comment_Rendering::render( $child_comment, array(), 1 );
		$child_content = ob_get_clean();
		
		$this->assertNotContains( 
			'contextual',
			$child_content,
			'Expected no contextual class in child content.'
		);
	}

	public function test_local_rendering() {
		$post_id = $this->factory->post->create();
		$comment = $this->factory->comment->create_and_get( array(
			'comment_post_ID' => $post_id,
            'comment_content' => 'Foo {{Bar}}',
		) );

		ob_start();
		Prompt_Email_Comment_Rendering::render( $comment, array(), 0 );
		$content = ob_get_clean();

		$this->assertContains(
		    'Foo',
            $content,
            'Expected plain text words from original content.'
        );

		$this->assertNotContains(
			'mailto:',
			$content,
			'Expected no mailto links in local rendering.'
		);

		$this->assertContains(
		    '\\{{Bar}}',
            $content,
            'Expected handlebars to be escaped.'
        );
	}
}