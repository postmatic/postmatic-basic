<?php

class SubscribeWidgetTest extends WP_UnitTestCase {

	/** @var Prompt_Subscribe_Widget */
	protected $widget;
	protected $args;
	protected $instance;

	function setUp() {
		parent::setUp();
		register_widget( 'Prompt_Subscribe_Widget' );
		$this->widget = new Prompt_Subscribe_Widget();
		$this->widget->id = $this->widget->id_base . '-1';
		$this->args = array(
			'before_widget' => 'BEFORE',
			'before_title' => 'TITLE:',
			'after_title' => ':TITLE',
			'after_widget' => 'AFTER',
		);
		$this->instance = array(
			'title' => 'Test Subscribe Widget',
			'collect_name' => true,
			'template_path' => null, // This argument was removed, but make sure it doesn't cause errors
			'list' => null,
		);
	}

	function getDynamicOutput() {
		ob_start();
		Prompt_Subscribe_Widget::render_dynamic_content( $this->widget->id, $this->instance );
		return ob_get_clean();
	}

	function testConstructor() {
		$this->assertEquals( 'Postmatic Subscribe', $this->widget->name, 'Expected widget name to be Postmatic Subscribe.' );
		$this->assertNotEmpty( $this->widget->widget_options['description'], 'Expected some description text.' );
	}

	function testWidgetContent() {
		ob_start();
		$this->widget->widget( $this->args, $this->instance );
		$this->verifyWidgetOutput( ob_get_clean() );
	}

	function testListWidgetContent() {
		$this->instance['list'] = new Prompt_Post( $this->factory->post->create() );
		ob_start();
		$this->widget->widget( $this->args, $this->instance );
		$this->verifyWidgetOutput( ob_get_clean() );
	}

	function testTheWidgetOutput() {
		ob_start();
		the_widget( 'Prompt_Subscribe_Widget', $this->instance, $this->args );
		$this->verifyWidgetOutput( ob_get_clean() );
	}

	function testTheWidgetOutputLoggedIn() {
		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		ob_start();
		the_widget( 'Prompt_Subscribe_Widget', $this->instance, $this->args );
		$this->verifyWidgetOutput( ob_get_clean() );

		wp_set_current_user( 0 );
	}

	function verifyWidgetOutput( $widget_output ) {
		$this->assertTrue( wp_script_is( 'prompt-subscribe-form' ), 'Expected subscribe form javascript to be enqueued.' );
		$this->assertContains( $this->instance['title'], $widget_output, 'Expected instance title in widget output.' );
		$this->assertContains( 'prompt-subscribe-widget-content', $widget_output, 'Expected to find the div container for the widget.' );
		$this->assertContains( 'data-widget-id', $widget_output, 'Expected to find the data attribute for the widget ID.' );
		$this->assertContains( 'data-collect-name', $widget_output, 'Expected to find the collect name data attribute.' );
		$this->assertContains( 'data-subscribe-prompt', $widget_output, 'Expected to find the subscribe prompt data attribute.' );
		$this->assertContains( 'data-list', $widget_output, 'Expected to find the list data attributes.' );
		if ( !empty( $this->instance['list'] ) ) {
			$this->assertContains(
				'data-list-type="' . get_class( $this->instance['list'] ) . '"',
				$widget_output,
				'Expected to find the target list type data attribute.'
			);
			$this->assertContains(
				'data-list-id="' . $this->instance['list']->id() . '"',
				$widget_output,
				'Expected to find the target list type data attribute.'
			);
		}
	}

	function testTheWidgetDefaultsOutput() {
		ob_start();
		the_widget( 'Prompt_Subscribe_Widget' );
		$widget_output = ob_get_clean();

		$this->assertContains( 'prompt-subscribe-widget-content', $widget_output, 'Expected to find the div container for the widget.' );
		$this->assertContains( 'data-widget-id', $widget_output, 'Expected to find the data attribute for the widget ID.' );
	}

	function testWidgetLoggedInUnsubscribed() {
		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$widget_output = $this->getDynamicOutput();

		$this->assertNotContains( 'subscribe_name', $widget_output, 'Expected name input in widget output.' );
		$this->assertNotContains( 'subscribe_email', $widget_output, 'Expected last name input in widget output.' );
		$this->assertContains( 'subscribe_submit', $widget_output, 'Expected submit button name in widget output.' );
		$this->assertContains( 'name="mode"', $widget_output, 'Expected the mode hidden input.' );
		$this->assertContains( 'value="subscribe"', $widget_output, 'Expected the subscribe as the submit value.' );

		wp_set_current_user( 0 );
	}

	function testWidgetLoggedInSubscribed() {
		$user_id = $this->factory->user->create();

		$site = new Prompt_Site();
		$site->subscribe( $user_id );
		$this->instance['list'] = $site;

		wp_set_current_user( $user_id );

		$widget_output = $this->getDynamicOutput();

		$this->assertNotContains( 'subscribe_name', $widget_output, 'Expected name input in widget output.' );
		$this->assertNotContains( 'subscribe_email', $widget_output, 'Expected last name input in widget output.' );
		$this->assertContains( 'subscribe_submit', $widget_output, 'Expected submit button name in widget output.' );
		$this->assertContains( 'value="unsubscribe"', $widget_output, 'Expected the unsubscribe as the submit value.' );

		wp_set_current_user( 0 );
	}

	function testWidgetLoggedOut() {

		wp_set_current_user( 0 );

		$widget_output = $this->getDynamicOutput();

		$this->assertContains( 'subscribe_name', $widget_output, 'Expected name input in widget output.' );
		$this->assertContains( 'subscribe_email', $widget_output, 'Expected last name input in widget output.' );
		$this->assertContains( 'subscribe_submit', $widget_output, 'Expected submit button name in widget output.' );
		$this->assertRegExp(
			'/<input[^>]*value="subscribe"/s',
			$widget_output,
			'Expected widget output to contain a subscribe button.'
		);
	}

	function testWidgetLoggedOutNoNameCollection() {

		wp_set_current_user( 0 );

		$this->instance['collect_name'] = false;

		$widget_output = $this->getDynamicOutput();

		$this->assertNotContains( 'subscribe_name', $widget_output, 'Expected no name input in widget output.' );
		$this->assertContains( 'subscribe_email', $widget_output, 'Expected last name input in widget output.' );
		$this->assertContains( 'subscribe_submit', $widget_output, 'Expected submit button name in widget output.' );
		$this->assertRegExp(
			'/<input[^>]*value="subscribe"/s',
			$widget_output,
			'Expected widget output to contain a subscribe button.'
		);
	}

	function testWidgetCommenterCookies() {

		wp_set_current_user( 0 );

		$author = 'Cookie Monster';
		$email = 'crumby@muppet.org';
		$_COOKIE['comment_author_' . COOKIEHASH] = $author;
		$_COOKIE['comment_author_email_' . COOKIEHASH] = $email;
		$_COOKIE['comment_author_url_' . COOKIEHASH] = '';

		$widget_output = $this->getDynamicOutput();

		$this->assertContains( $author, $widget_output, 'Expected comment author name in widget output.' );
		$this->assertContains( $email, $widget_output, 'Expected comment author email in widget output.' );
	}

	function testWidgetOnAuthor() {

		$author = $this->factory->user->create_and_get();
		$this->instance['list'] = new Prompt_User( $author );

		$widget_output = $this->getDynamicOutput();

		$this->assertContains( 'value="Prompt_User"', $widget_output, 'Expected a user subscription widget.' );
		$this->assertContains(
			'value="' . $author->ID . '"',
			$widget_output,
			'Expected the author ID in the subscribe widget.'
		);

		wp_reset_query();
	}

	function testWidgetTranslated() {
		add_filter( 'gettext', array( $this, 'translateSubscribe' ), 10, 3 );

		$widget_output = $this->getDynamicOutput();

		$this->assertContains( 'value="subscribe"', $widget_output, 'Expected the mode value NOT to be translated.' );
		$this->assertContains( 'value="ebircsbus"', $widget_output, 'Expected the submit value to be translated.' );

		remove_filter( 'gettext', array( $this, 'translateSubscribe' ), 10 );
	}

	function translateSubscribe( $custom, $original, $domain ) {
		if ( 'Postmatic' != $domain )
			return $custom;

		if ( 'subscribe' != $original )
			return $custom;

		return 'ebircsbus';
	}

	function testFormTitle() {

		ob_start();
		$this->widget->form( $this->instance );
		$form_output = ob_get_clean();

		$this->assertContains( $this->instance['title'], $form_output, 'Expected form output to contain title.' );

	}

	function testFormCollectNameCheckedDefault() {

		ob_start();
		$this->widget->form( array() );
		$form_output = ob_get_clean();

		$this->assertRegExp(
			'/collect_name[^>]*checked/',
			$form_output,
			'Expected collect name to be checked by default.'
		);
	}

	function testFormCollectNameChecked() {

		ob_start();
		$this->widget->form( array( 'collect_name' => true ) );
		$form_output = ob_get_clean();

		$this->assertRegExp(
			'/collect_name[^>]*checked/',
			$form_output,
			'Expected collect name to be checked.'
		);
	}

	function testFormCollectNameUnchecked() {

		ob_start();
		$this->widget->form( array( 'collect_name' => false ) );
		$form_output = ob_get_clean();

		$this->assertNotRegExp(
			'/collect_name[^>]*checked/',
			$form_output,
			'Expected collect name to be unchecked.'
		);
	}


}