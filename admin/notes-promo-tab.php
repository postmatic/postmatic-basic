<?php

/**
 * A tab to promote Notes
 * @since 2.0.0
 */
class Prompt_Admin_Notes_Promo_Tab extends Prompt_Admin_Options_Tab {

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function name() {
		return __( 'Postmatic Notes', 'Postmatic' );
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function slug() {
		return 'notes';
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	public function render() {
		$template = new Prompt_Template( 'notes-promo.php' );
		return $template->render();
	}

}
