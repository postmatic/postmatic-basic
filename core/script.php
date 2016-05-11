<?php

class Prompt_Script {

	/** @var  string */
	protected $handle;
	/** @var  string */
	protected $path;
	/** @var  string */
	protected $url;
	/** @var  array */
	protected $dependencies;
	/** @var  string */
	protected $version;
	/** @var  boolean */
	protected $in_footer;

	/**
	 * @param array $properties {
	 *      Script properties
	 *      @type string $handle Unique script identifier
	 *      @type string $path Path to script from plugin root
	 *      @type array  $dependencies Optional dependencies
	 *      @type string $version Defaults to plugin version
	 *      @type boolean $in_footer Defaults to true
	 * }
	 */
	public function __construct( $properties ) {

		$defaults = array(
			'dependencies' => array(),
			'version' => Prompt_Core::version(),
			'in_footer' => true,
		);

		$properties = wp_parse_args( $properties, $defaults );

		foreach ( $properties as $name => $value ) {
			$this->{$name} = $value;
		}

		$suffix = '.min';

		if ( defined( 'WP_SCRIPT_DEBUG' ) and WP_SCRIPT_DEBUG )
			$suffix = '';

		if ( !file_exists( Prompt_Core::$dir_path . '/version' ) )
			$suffix = '';

		if ( $suffix )
			$this->path = preg_replace( '/(\.[^\.]*$)/', $suffix . '\1', $this->path );

		$this->url = path_join( Prompt_Core::$url_path, $this->path );
	}

	/**
	 * @return array
	 */
	public function get_dependencies() {
		return $this->dependencies;
	}

	/**
	 * @return string
	 */
	public function get_handle() {
		return $this->handle;
	}

	/**
	 * @return boolean
	 */
	public function get_in_footer() {
		return $this->in_footer;
	}

	/**
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	public function register() {
		wp_register_script(
			$this->handle,
			$this->url,
			$this->dependencies,
			$this->version,
			$this->in_footer
		);
	}

	public function enqueue() {
		$this->register();
		wp_enqueue_script( $this->handle );
	}

	public function localize( $object_name, $data ) {
		$this->register();
		wp_localize_script( $this->handle, $object_name, $data );
	}
}