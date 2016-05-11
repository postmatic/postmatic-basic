<?php

class ViewHandlingTest extends WP_UnitTestCase {

	function testTemplateRedirectWithBadParams() {

		$redirect_mock = $this->getMock( 'Redirect_Mock', array( 'wp_redirect' ) );
		$redirect_mock->expects( $this->once() )
			->method( 'wp_redirect' )
			->with( home_url(), 302 )
			->willReturn( false );

		add_filter( 'wp_redirect', array( $redirect_mock, 'wp_redirect' ), 10, 2 );

		set_query_var( 'postmatic_view', 'foo' );

		Prompt_View_Handling::template_redirect();

		set_query_var( 'postmatic_view', null );

		remove_filter( 'wp_redirect', array( $redirect_mock, 'wp_redirect' ) );
	}
}