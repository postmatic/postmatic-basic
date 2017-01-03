<?php

/**
 * Interface for getting information about the current license status.
 *
 * @since 2.1.0
 */
interface Prompt_Interface_License_Status {

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