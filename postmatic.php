<?php
/*
  Plugin Name: Replyable - Subscribe to Comments & Reply by email
  Version: 2.2.5
  License: GPL2+
  Author: Postmatic
  Description: Two-way email commenting for WordPress. Subscribe to Comments reimagined. Smarter. More contextual. Mobile ready. Replyable.
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

if ( class_exists( 'Prompt_Core' ) and Prompt_Core::$dir_path ) {
	// Allow others to use Prompt as a dependency
	return;
}

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

if ( !class_exists( 'Prompt_Root' ) ) {
	/**
	 * Manage things that must be done from the root plugin file.
	 * @since 2.0.0
	 * @return Freemius Freemius instance.
	 */
	class Prompt_Root {
		public static function load_freemius() {
			$freemius = new Prompt_Freemius( Prompt_Core::$options );
			$freemius->load();
			return $freemius;
		}
	}
}

Prompt_Core::load();

