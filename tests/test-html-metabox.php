<?php

class HtmlMetaboxTest extends WP_UnitTestCase {
	
	protected static $id = 'test_html_metabox';
	protected static $custom_text_name = 'prompt_custom_html';
	protected static $enable_custom_text_name = 'prompt_enable_custom_html';

	function tearDown() {
		wp_set_current_user( 0 );
	}

	function makeMetabox() {

		return new Prompt_Admin_HTML_Metabox(
			self::$id,
			'Test HTML Metabox',
			array( 'post_type' => array( 'post' ) )
		);

	}

	function makeLoggedInAuthorPost( $status = 'draft' ) {
		$author_id = $this->factory->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $author_id );
		$post = $this->factory->post->create_and_get( array( 'post_status' => $status, 'post_author' => $author_id ) );
		return $post;
	}

	function testMake() {
		$instance = Prompt_Admin_HTML_Metabox::make();
		$this->assertInstanceOf( 'Prompt_Admin_HTML_Metabox', $instance );
	}
	
	function testPrintPublishBoxMessageOkay() {

		$post = $this->factory->post->create_and_get();

		ob_start();
		Prompt_Admin_HTML_Metabox::print_publish_box_message( $post );
		$message = ob_get_clean();

		$this->assertContains( '#prompt_custom_html_metabox', $message, 'Expected a link to the metabox.' );
		$this->assertContains( 'ok', $message, 'Expected status to be Okay.' );
	}
		
	function testPrintPublishBoxCustomizedMessageOkay() {

		$post = $this->factory->post->create_and_get( array( 'post_content' => 'a [random] shortcode' ) );
		
		$prompt_post = new Prompt_Post( $post );
		
		$prompt_post->set_custom_html( 'edited content' );

		ob_start();
		Prompt_Admin_HTML_Metabox::print_publish_box_message( $post );
		$message = ob_get_clean();

		$this->assertContains( '#prompt_custom_html_metabox', $message, 'Expected a link to the metabox.' );
		$this->assertContains( 'ok', $message, 'Expected status to be Okay.' );
	}
	
	function testPrintPublishBoxMessageCheck() {

		$post = $this->factory->post->create_and_get( array( 'post_content' => 'a [random] shortcode' ) );

		ob_start();
		Prompt_Admin_HTML_Metabox::print_publish_box_message( $post );
		$message = ob_get_clean();

		$this->assertContains( '#prompt_custom_html_metabox', $message, 'Expected a link to the metabox.' );
		$this->assertContains( 'check', $message, 'Expected status to be Check.' );
	}
	
	function testRegister() {

		set_current_screen( 'post' );

		$box = $this->makeMetabox();
		$box->register();

		$this->assertNotEmpty(
			$GLOBALS['wp_meta_boxes']['post']['advanced']['default'],
			'Expected the meta box to be registered.'
		);
	}
	
	function testDefaultDisplay() {

		$post = $this->makeLoggedInAuthorPost();
		$_GET['post'] = $post->ID;

		$box = $this->makeMetabox();

		ob_start();
		$box->display( $post );
		$content = ob_get_clean();

		$this->assertContains( 'prompt-customize-html', $content, 'Expected a customize button in the content.' );
	}

	function testCustomDraftDisplay() {
		$post = $this->makeLoggedInAuthorPost();

		$custom_html = '<p>CUSTOM HTML</p>';
		$prompt_post = new Prompt_Post( $post );
		$prompt_post->set_custom_html( $custom_html );

		$_GET['post'] = $post->ID;

		$box = $this->makeMetabox();

		ob_start();
		$box->display( $post );
		$content = ob_get_clean();

		$this->assertContains( '<textarea', $content, 'Expected a textarea tag in the content.' );
		$this->assertContains( $custom_html, $content, 'Expected the custom HTML in the content.' );
		$this->assertNotRegExp( '/.input[^>]*prompt-customize-html/', $content, 'Expected no customize button.' );
	}

	function testCustomPublishedDisplay() {
		$post = $this->makeLoggedInAuthorPost( 'publish' );

		$custom_html = '<p>CUSTOM HTML</p>';
		$prompt_post = new Prompt_Post( $post );
		$prompt_post->add_sent_recipient_ids( array( 1 ) );
		$prompt_post->set_custom_html( $custom_html );

		$_GET['post'] = $post->ID;

		$box = $this->makeMetabox();

		ob_start();
		$box->display( $post );
		$content = ob_get_clean();

		$this->assertContains( $custom_html, $content, 'Expected the custom HTML in the content.' );
	}

	function testDoNotSaveCustom() {
		$post = $this->makeLoggedInAuthorPost();

		$custom_html = '<p>CUSTOM HTML</p>';
		$_POST[self::$custom_text_name] = $custom_html;
		$_POST[self::$enable_custom_text_name] = '';
		$_POST['post_ID'] = $post->ID;
		$_POST['action'] = 'editpost';

		$box = $this->makeMetabox();

		$box->_save_post( $post->ID, $post );

		$prompt_post = new Prompt_Post( $post );

		$this->assertEmpty( $prompt_post->get_custom_html(), 'Expected NO custom HTML to be set.' );
	}

	function testSaveCustom() {
		$post = $this->makeLoggedInAuthorPost();

		$custom_html = '<p>CUSTOM HTML</p>';
		$_POST[self::$custom_text_name] = $custom_html;
		$_POST[self::$enable_custom_text_name] = '1';
		$_POST['post_ID'] = $post->ID;
		$_POST['action'] = 'editpost';

		$box = $this->makeMetabox();

		$box->_save_post( $post->ID, $post );

		$prompt_post = new Prompt_Post( $post );

		$this->assertEquals( $custom_html, $prompt_post->get_custom_html(), 'Expected custom HTML to be set.' );
	}

}