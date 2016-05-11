<?php

class DeliveryMetaboxTest extends WP_UnitTestCase {
	/** @var string */
	static protected $no_email_name = 'prompt_no_email';
	/** @var string */
	static protected $no_featured_image_name = 'prompt_no_featured_image';
	/** @var string */
	static protected $excerpt_only_name = 'prompt_excerpt_only';

	function testNoEmailDefault() {
		$post_id = $this->factory->post->create();

		$this->assertFalse(
			Prompt_Admin_Delivery_Metabox::suppress_email( $post_id ),
			'Expected default for post email suppression to be false.'
		);

		Prompt_Core::$options->set( 'no_post_email_default', true );

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_email( $post_id ),
			'Expected default for post email suppression to be changed to true.'
		);

		Prompt_Core::$options->reset();
	}

	function testNoFeaturedImageDefault() {
		$post_id = $this->factory->post->create();

		$this->assertFalse(
			Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post_id ),
			'Expected default for post featured image suppression to be false.'
		);

		Prompt_Core::$options->set( 'no_post_featured_image_default', true );

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post_id ),
			'Expected default for post featured image suppression to be changed to true.'
		);

		Prompt_Core::$options->reset();
	}

	function testExcerptDefault() {
		$post_id = $this->factory->post->create();

		$this->assertFalse(
			Prompt_Admin_Delivery_Metabox::excerpt_only( $post_id ),
			'Expected default for excerpt only to be false.'
		);

		Prompt_Core::$options->set( 'no_post_email_default', true );

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_email( $post_id ),
			'Expected default for post email suppression to be changed to true.'
		);

		Prompt_Core::$options->reset();
	}

	function testNoEmailPost() {
		$post_id = $this->factory->post->create();

		$_POST['post_ID'] = $post_id;
		$_POST[self::$no_email_name] = true;

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_email( $post_id ),
			'Expected post email suppression to be true.'
		);

		unset( $_POST[self::$no_email_name] );
		unset( $_POST['post_ID'] );
	}

	function testNoFeaturedImagePost() {
		$post_id = $this->factory->post->create();

		$_POST['post_ID'] = $post_id;
		$_POST[self::$no_featured_image_name] = true;

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post_id ),
			'Expected post featured image suppression to be true.'
		);

		unset( $_POST[self::$no_email_name] );
		unset( $_POST['post_ID'] );
	}

	function testExcerptPost() {
		$post_id = $this->factory->post->create();

		$_POST['post_ID'] = $post_id;
		$_POST[self::$excerpt_only_name] = true;

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::excerpt_only( $post_id ),
			'Expected excerpt only to be true.'
		);

		unset( $_POST[self::$excerpt_only_name] );
		unset( $_POST['post_ID'] );
	}

	function testNoEmailMeta() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, self::$no_email_name, true );

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_email( $post_id ),
			'Expected post email suppression to be true.'
		);
	}

	function testNoFeaturedImageMeta() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, self::$no_featured_image_name, true );

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post_id ),
			'Expected post featured image suppression to be true.'
		);
	}

	function testExcerptMeta() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, self::$excerpt_only_name, true );

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::excerpt_only( $post_id ),
			'Expected excerpt only to be true.'
		);
	}

	function testSaveNoFeaturedImageDefault() {
		$post = $this->factory->post->create_and_get();

		$_POST['post_ID'] = $post->ID;
		$_POST['action'] = 'editpost';
		$_POST[self::$no_featured_image_name] = true;

		$metabox = Prompt_Core::delivery_metabox();
		$metabox->_save_post( $post->ID, $post );

		$this->assertTrue(
			Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post->ID ),
			'Expected post featured image suppression to be true.'
		);

		$this->assertTrue(
			Prompt_Core::$options->get( 'no_post_featured_image_default' ),
			'Expected no post featured image default to be changed.'
		);
	}

	function testLocalMailDisplay() {
		Prompt_Core::$options->set( 'email_transport', Prompt_Enum_Email_Transports::LOCAL );

		$post = $this->factory->post->create_and_get();

		$metabox = Prompt_Core::delivery_metabox();

		ob_start();
		$metabox->display( $post );
		$content = ob_get_clean();

		$this->assertNotContains(
			self::$no_featured_image_name,
			$content,
			'Expected no featured image suppression with local mail.'
		);

		Prompt_Core::$options->reset();
	}

	function testPostDeliveryDisabled() {
		Prompt_Core::$options->set( 'enable_post_delivery', false );

		$this->assertNull( Prompt_Core::delivery_metabox(), 'Expected no delivery metabox with post delivery disabled.' );
		$this->assertNull( Prompt_Core::html_metabox(), 'Expected no HTML metabox with post delivery disabled.' );

		Prompt_Core::$options->reset();
	}
}
