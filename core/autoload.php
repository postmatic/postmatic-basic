<?php

/**
 * Autoload prompt classes based on class name.
 *
 * Doesn't stat any files unless the class name matches our criteria.
 *
 * @since 1.0.0
 *
 */
class Prompt_Autoload {
	/** @var  string */
	protected $prefix;
	/** @var  string */
	protected $basedir;
	/** @var  string */
	protected $suffix;

	/**
	 * @since 2.0.0 Added suffix
	 * @since 1.0.0
	 *
	 * @param string $prefix
	 * @param string $basedir
	 * @param string $suffix
	 */
	protected function __construct( $prefix, $basedir, $suffix = '' ) {
		$this->prefix = $prefix;
		$this->basedir = $basedir;
		$this->suffix = $suffix;
	}

	/**
	 * @since 2.0.0 Added suffix
	 * @since 1.0.0
	 *
	 * @param string $prefix
	 * @param string $basedir
	 * @param string $suffix
	 */
	static function register( $prefix, $basedir, $suffix = '' ) {
		$loader = new self( $prefix, $basedir, $suffix );

		spl_autoload_register( array( $loader, 'autoload' ) );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $class
	 */
	function autoload( $class ) {
		if ( $class[0] === '\\' ) {
			$class = substr( $class, 1 );
		}

		if ( strpos( $class, $this->prefix ) !== 0 ) {
			return;
		}

		if ( $this->suffix and strpos( $class, $this->suffix ) === false ) {
			return;
		}

		$path = str_replace( $this->prefix, '', $class );
		$path = str_replace( '_', '-', strtolower( $path ) );

		$file = sprintf( '%s/%s.php', $this->basedir, $path );

		if ( is_file( $file ) ) {
			require $file;
		}
	}
}
