<?php

/**
 * A tab to promote Analytics
 * @since 2.0.0
 */
class Prompt_Admin_Analytics_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function name() {
		return __( 'Postmatic Analytics', 'Postmatic' );
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function slug() {
		return 'analytics';
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function render() {
		$template = new Prompt_Template( 'analytics-promo.php' );
		return $template->render();
	}

}
