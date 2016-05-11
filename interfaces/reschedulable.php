<?php

/**
 * An interface for tasks that can be rescheduled
 */
interface Prompt_Interface_Reschedulable {
	/**
	 * Setting to null should use the default for the task, i.e. the first attempt. Setting to a number indicates
	 * the current request is a retry.
	 *
	 * @since 2.0.0
	 *
	 * @param null|int $seconds
	 * @return object
	 */
	function set_retry_wait_seconds( $seconds = null );
}
