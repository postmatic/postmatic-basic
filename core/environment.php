<?php

class Prompt_Environment {

	/** @var  string */
	protected $prompt_version;
	/** @var  array */
	protected $prompt_options;
	/** @var  string */
	protected $php_version;
	/** @var  array */
	protected $php_extensions;
	/** @var  string */
	protected $wp_version;
	/** @var  string */
	protected $db_version;
	/** @var  string */
	protected $siteurl;
	/** @var  boolean */
	protected $is_multisite;
	/** @var  array */
	protected $active_plugins;
	/** @var  array */
	protected $active_sitewide_plugins;
	/** @var  array */
	protected $plugins;
	/** @var  array */
	protected $theme;

	public function __construct() {

		$this->prompt_version = Prompt_Core::version( $full = true );
		$this->prompt_options = array_diff_key( Prompt_Core::$options->get(), array( 'prompt_key' => '' ) );

		$this->php_version = phpversion();
		$this->php_extensions = get_loaded_extensions();
		$this->wp_version = $GLOBALS['wp_version'];
		$this->db_version = $GLOBALS['wpdb']->db_version();

		$this->siteurl = get_option( 'siteurl' );
		$this->is_multisite = is_multisite();
		$this->active_plugins = get_option( 'active_plugins' );
		$this->active_sitewide_plugins = get_option( 'active_sitewide_plugins' );
		$this->plugins = get_plugins();

		$this->theme = array();
		$theme = wp_get_theme();
		$theme_fields = array( 'Name', 'ThemeURI', 'Author', 'AuthorURI', 'Version', 'Template' );
		foreach ( $theme_fields as $field ) {
			$this->theme[$field] = $theme->get( $field );
		}
	}

	/**
	 * @return array
	 */
	public function get_active_plugins() {
		return $this->active_plugins;
	}

	/**
	 * @return array
	 */
	public function get_active_sitewide_plugins() {
		return $this->active_sitewide_plugins;
	}

	/**
	 * @return string
	 */
	public function get_db_version() {
		return $this->db_version;
	}

	/**
	 * @return string
	 */
	public function get_php_version() {
		return $this->php_version;
	}

	/**
	 * @return boolean
	 */
	public function get_is_multsite() {
		return $this->is_multisite;
	}

	/**
	 * @return array
	 */
	public function get_plugins() {
		return $this->plugins;
	}

	/**
	 * @return string
	 */
	public function get_wp_version() {
		return $this->wp_version;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}
}