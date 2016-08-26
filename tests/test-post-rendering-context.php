<?php

class PostRenderingContextTest extends WP_UnitTestCase {

	function testDefaultTextContent() {

		$text = 'TEST TEXT';
		$html = '<p>' . $text . '</p>';

		$post = $this->factory->post->create_and_get( array( 'post_content' => $html ) );

		$context = new Prompt_Post_Rendering_Context( $post, null );

		$this->assertEmpty( get_post(), 'Expected no current post.' );

		$check_text = $context->get_the_text_content();
		$context->reset();

		$this->assertContains( $text, $check_text );
		$this->assertNotContains( '<p>', $check_text );
		$this->assertEquals( $post->ID, get_the_ID(), 'Expected test post to be current global' );
	}

	function testExcerptTextContent() {

		Prompt_Core::$options->set( 'excerpt_default', true );

		$excerpt_text = 'EXCERPT TEXT';
		$excerpt_html = '<p>' . $excerpt_text . '</p>';

		$content_text = 'TEST TEXT';
		$content_html = '<p>' . $content_text . '</p>';

		$post = $this->factory->post->create_and_get( array(
			'post_excerpt' => $excerpt_html,
			'post_content' => $content_html,
		) );

		$context = new Prompt_Post_Rendering_Context( $post, array() );
		$check_text = $context->get_the_text_content();
		$context->reset();

		$this->assertContains( $excerpt_text, $check_text );
		$this->assertNotContains( $content_text, $check_text );

		Prompt_Core::$options->reset();
	}

	function testModifier() {

		$post = $this->factory->post->create_and_get();

		$modifier_mock =  $this->getMock( 'Prompt_Post_Rendering_Modifier' );
		$modifier_mock->expects( $this->once() )->method( 'setup' );
		$modifier_mock->expects( $this->once() )->method( 'reset' );

		$context = new Prompt_Post_Rendering_Context( $post, array( $modifier_mock ) );

		$context->setup();
		$context->reset();
	}

	function testDefaultModifiers() {

		$filterMock = $this->getMock( 'MockFilter', array( 'verify_modifiers' ) );
		$filterMock->expects( $this->once() )
			->method( 'verify_modifiers' )
			->will( $this->returnCallback( array( $this, 'verifyDefaultModifiers' ) ) );

		add_filter( 'prompt/post_rendering_context/modifiers', array( $filterMock, 'verify_modifiers' ) );

		$post = $this->factory->post->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post );
		$context->setup();
		$context->reset();

	}

	function verifyDefaultModifiers( $modifiers ) {
		$this->assertCount( 3, $modifiers, 'Expected three default modifiers.' );
		return $modifiers;
	}

	function testGetAuthor() {
		$author = $this->factory->user->create_and_get();
		$post = $this->factory->post->create_and_get( array( 'post_author' => $author->ID ) );

		$context = new Prompt_Post_Rendering_Context( $post, array() );

		$context->setup();

		$context_author = $context->get_author();

		$this->assertInstanceOf( 'Prompt_User', $context_author );
		$this->assertEquals( $author->ID, $context_author->id(), 'Expected the original author ID' );

		$context->reset();
	}

	function testGetPost() {

		$post = $this->factory->post->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post, array() );

		$this->assertInstanceOf( 'Prompt_Post', $context->get_post() );
		$this->assertEquals( $post->ID, $context->get_post()->id() );
	}

	function testGetSite() {

		$post = $this->factory->post->create_and_get();

		$context = new Prompt_Post_Rendering_Context( $post, array() );

		$this->assertInstanceOf( 'Prompt_Site', $context->get_site() );
	}

}