<?php

interface Prompt_Interface_Http_Client {

	function send( $endpoint, $request = array() );
	function get( $endpoint, $request = array() );
	function post( $endpoint, $request = array() );
	function head( $endpoint, $request = array() );
	function put( $endpoint, $request = array() );
	function delete( $endpoint, $request = array() );

}