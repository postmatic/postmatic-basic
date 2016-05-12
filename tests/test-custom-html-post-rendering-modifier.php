<?php

class CustomHTMLPostRenderingModifierTest extends WP_UnitTestCase {

	function testCustomContent() {

		$prompt_post = new Prompt_Post( $this->factory->post->create() );
		
		query_posts( array( 'p' => $prompt_post->id() ) );
		
		the_post();
		
		$modifier = new Prompt_Custom_HTML_Post_Rendering_Modifier();

		$modifier->setup();

		$this->assertContains( 
			$prompt_post->get_wp_post()->post_content, 
			apply_filters( 'the_content', get_the_content() ), 
			'Expected original content.' 
		);

		$custom_html = '<p>CUSTOM HTML</p>';
		$prompt_post->set_custom_html( $custom_html );

		$this->assertContains(
			$custom_html,
			apply_filters( 'the_content', get_the_content() ),
			'Expected customized content.'
		);
		
		$modifier->reset();
	}
}
