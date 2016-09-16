<?php

/**
 * Base class for simple fuzzy text matching.
 * @since 2.0.0
 */
abstract class Prompt_Matcher {

	/** @var  string */
	protected $text;

	/**
	 * Text that will be considered an exact match.
	 * @since 2.0.0
	 * @return string
	 */
	public static function target() {
		return '';
	}

	/**
	 * @since 2.0.0
	 * @param string $text
	 */
	public function __construct( $text = '' ) {
		$this->text = $text;
	}

	/**
	 * @since 2.0.0
	 * @return boolean  Whether the text matches an expectation
	 */
	public function matches() {
		return false;
	}

	/**
	 * Remove characters from the text that don't affect the match.
	 * @since 2.0.0
	 * @return string
	 */
	protected function stripped_text() {
		$stripped = preg_replace( '/^[\s\pZ\pC]+/u', '', $this->text );
		$stripped = preg_replace( '/[\*\_\?]/', '', $stripped );
		return $stripped;
	}
}