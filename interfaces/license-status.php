<?php

/**
 * Interface for getting information about the current license status.
 *
 * @since 2.1.0
 */
interface Prompt_Interface_License_Status {

	/**
	 * Whether the site is still pending activation.
	 *
	 * When this is the case, license data is not yet available.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	function is_pending_activation();

	/**
	 * Whether a trial is available to start.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	function is_trial_available();

	/**
	 * Whether a trial has started but not expired.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	function is_trial_underway();

	/**
	 * Whether a premium license is in effect.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	function is_paying();

}