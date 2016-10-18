<?php
/*
  Plugin Name: Postmatic Basic
  Description:
  Version: 2.1.0
  License: GPL2+
  Author: Postmatic
  Author URI: http://gopostmatic.com/
  Text Domain: Postmatic
  Domain Path: /lang
  Minimum WordPress Version Required: 4.3
 */

/*
  Copyright (c) 2016 Transitive, Inc

  This program is free software; you can redistribute it
  and/or modify it under the terms of the GNU General Public
  License as published by the Free Software Foundation;
  either version 2 of the License, or (at your option) any
  later version.

  This program is distributed in the hope that it will be
  useful, but WITHOUT ANY WARRANTY; without even the implied
  warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
  PURPOSE. See the GNU General Public License for more
  details.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( class_exists( 'Prompt_Core' ) ) {
	// Allow others to use Prompt as a dependency
	return;
}

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

Prompt_Autoload::register( 'Prompt_Interface_', dirname( __FILE__ ) . '/interfaces' );
Prompt_Autoload::register( 'Prompt_Admin_', dirname( __FILE__ ) . '/admin' );
Prompt_Autoload::register( 'Prompt_Enum_', dirname( __FILE__ ) . '/enums' );

Prompt_Core::load();

if ( !class_exists( 'Prompt_Root' ) ) {
	/**
	 * Manage things that must be done from the root plugin file.
	 * @since 2.0.0
	 */
	class Prompt_Root {
	}
}
