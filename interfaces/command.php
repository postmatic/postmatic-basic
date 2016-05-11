<?php

interface Prompt_Interface_Command {

	function get_keys();
	function set_keys( $keys );
	function get_message();
	function set_message( $message );
	function execute();

}