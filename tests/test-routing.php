<?php

class RoutingTest extends WP_UnitTestCase {

	function test_template_redirect_with_no_query_var() {

		$filter_mock = $this->getMockBuilder( 'Filter_Mock' )->setMethods( array( 'wp_die_handler' ) )->getMock();
		$filter_mock->expects( $this->never() )->method( 'wp_die_handler' );

		add_filter( 'wp_die_handler', array( $filter_mock, 'wp_die_handler' ), 5 );

		Prompt_Routing::template_redirect( false );

		remove_filter( 'wp_die_handler', array( $filter_mock, 'wp_die_handler' ) );
	}

	function test_template_redirect_with_bad_params() {

		$filter_mock = $this->getMockBuilder( 'Filter_Mock' )->setMethods( array( 'wp_die_handler' ) )->getMock();
		$filter_mock->expects( $this->never() )->method( 'wp_die_handler' );

		add_filter( 'wp_die_handler', array( $filter_mock, 'wp_die_handler' ), 5 );

		set_query_var( 'postmatic_route', 'foo' );

		Prompt_Routing::template_redirect( false );

		set_query_var( 'postmatic_route', null );

		remove_filter( 'wp_die_handler', array( $filter_mock, 'wp_die_handler' ) );
	}

	function test_unsubscribe_url_without_list() {
		$url = Prompt_Routing::unsubscribe_url( 1 );
		$this->assertContains( 'postmatic_route=unsubscribe', $url, 'Expected route query argument.' );
		$this->assertContains( 'u=1', $url, 'Expected user query argument.' );
		$this->assertNotContains( 'l=', $url, 'Expected NO list query argument.' );
		$this->assertContains( 't=', $url, 'Expected token query argument.' );
		$this->assertContains( 's=', $url, 'Expected signature query argument.' );
	}

	function test_unsubscribe_url_with_list() {
		$url = Prompt_Routing::unsubscribe_url( 1, 'foo/1' );
		$this->assertContains( 'postmatic_route=unsubscribe', $url, 'Expected route query argument.' );
		$this->assertContains( 'u=1', $url, 'Expected user query argument.' );
		$this->assertContains( 'l=foo%2F1', $url, 'Expected list query argument.' );
		$this->assertContains( 't=', $url, 'Expected token query argument.' );
		$this->assertContains( 's=', $url, 'Expected signature query argument.' );
	}
}
