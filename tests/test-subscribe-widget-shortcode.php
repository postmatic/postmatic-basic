<?php

class SubscribeWidgetShortcodeTest extends WP_UnitTestCase {

	protected $instance;

	function setUp() {
		parent::setUp();
		add_action( 'the_widget', array( $this, 'verifyWidget' ), 10, 3 );
	}

	function tearDown() {
		remove_action( 'the_widget', array( $this, 'verifyWidget' ), 10 );
		parent::tearDown();
	}

	function verifyWidget( $widget, $instance, $args ) {
		$this->instance = $instance;
	}

	function testContent() {
		$content = Prompt_Subscribe_Widget_Shortcode::render( array() );

		$this->assertNotEmpty( $this->instance, 'Expected widget args to be set.' );
		$this->assertContains( 'prompt-subscribe-widget-content', $content, 'Expected widget class in content.' );

	}

	/**
	 * @dataProvider instanceProvider
	 */
	function testInstance( $given, $expected ) {

		Prompt_Subscribe_Widget_Shortcode::render( $given );

		$this->assertEquals( $expected, $this->instance );
	}

	function instanceProvider() {
		return array(
			array(
				array(),
				array(
					'title' => '',
					'collect_name' => true,
					'template_path' => null,
					'subscribe_prompt' => null,
					'list' => null,
				)
			),
			array(
				array( 'title' => 'TITLE', 'collect_name' => 'false', 'subscribe_prompt' => 'Prompt:' ),
				array(
					'title' => 'TITLE',
					'collect_name' => false,
					'template_path' => null,
					'subscribe_prompt' => 'Prompt:',
					'list' => null,
				)
			),
			array(
				array( 'title' => '', 'collect_name' => 'true' ),
				array(
					'title' => '',
					'collect_name' => true,
					'template_path' => null,
					'subscribe_prompt' => null,
					'list' => null,
				)
			),
			array(
				array( 'title' => '', 'list' => 'user/1' ),
				array(
					'title' => '',
					'collect_name' => true,
					'template_path' => null,
					'subscribe_prompt' => null,
					'list' => new Prompt_User( 1 ),
				)
			)
		);
	}
}
