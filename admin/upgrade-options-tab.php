<?php

/**
 * Upgrade options tab.
 *
 * @since 2.1.0
 */
class Prompt_Admin_Upgrade_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 * @since 2.1.0
	 * @return string
	 */
	public function name() {
		return __( 'Upgrade', 'Postmatic' );
	}

	/**
	 * @since 2.1.0
	 * @return string
	 */
	public function render() {
		$template = new Prompt_Template( 'upgrade-options.php' );
		return $template->render();
	}

}
