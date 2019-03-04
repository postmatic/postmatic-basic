<?php

/**
 * Manage the error record
 * @since 1.0.0
 */

class Prompt_Logging {

	/**
	 * @since 2.0.0
	 * @type string
	 */
	protected static $log_option_name = 'prompt_log';

	/**
	 * @since 2.0.0
	 * @type string
	 */
	protected static $last_submit_option_name = 'prompt_error_submit_time';

	/**
	 * Shortcut to add a WP_Error to the log.
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Error $error
	 * @return WP_Error
	 */
	public static function add_wp_error( WP_Error $error ) {
		return self::add_error(
			$error->get_error_code(),
			$error->get_error_message(),
			$error->get_error_data()
		);
	}

	/**
	 * Record an error.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code
	 * @param string $message
	 * @param mixed $data
	 * @return WP_Error
	 */
	public static function add_error( $code = '', $message = '', $data = array() ) {

		if ( is_array( $data ) ) {
			$data['backtrace'] = unserialize( serialize( debug_backtrace() ) );
		} else if ( $data instanceof WP_Error ) {
			$data->add_data( unserialize( serialize( debug_backtrace() ) ), 'backtrace' );
		}

		$wp_error = new WP_Error( $code, $message, $data );

		$log = self::get_log();

		if ( !$log ) {
			$log = array();
		}

		// If we go over 25 messages, only keep the most recent 20
		if ( count( $log ) > 25 )
			$log = array_slice( $log, 0, 20 );

		$time = time();

		array_unshift( $log, compact( 'time', 'code', 'message', 'data' ) );

		update_option( self::$log_option_name, $log, $autoload = 'no' );

		// Puke a little in dev environments
		trigger_error( $message, E_USER_NOTICE );

        if (
            Prompt_Core::$options->get('enable_collection') &&
            ! Prompt_Core::$options->get('suppress_error_submissions')
        ) {
            self::submit($message);
            Prompt_Core::$options->set('suppress_error_submissions', true);
        }

		return $wp_error;
	}

	/**
	 * Get saved error log entries.
	 *
	 * @since 1.0.0
	 *
	 * @param int $since Include only entries more recent than this timestamp.
	 * @param string $data_format Specify ARRAY_A to convert data to array format.
	 * @return array
	 */
	public static function get_log( $since = 0, $data_format = OBJECT ) {
		$log = get_option( self::$log_option_name );

		if ( !is_array( $log ) ) {
			$log = json_decode( $log );
		}

		if ( !$log ) {
			$log = array();
			add_option( self::$log_option_name, $log, '', $autoload = false );
		}

		$filtered_log = array();

		foreach ( $log as $entry ) {
			$entry = (array) $entry;

			if ( $data_format == ARRAY_A )
				$entry['data'] = self::object_to_array( $entry['data'] );

			if ( $entry['time'] > $since )
				$filtered_log[] = $entry;
		}

		return $filtered_log;
	}

	/**
	 * Forget recorded errors.
	 *
	 * @since 1.0.0
	 */
	public static function delete_log() {
		delete_option( self::$log_option_name );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return int Unix time
	 */
	public static function get_last_submission_time() {
		return absint( get_option( self::$last_submit_option_name ) );
	}

    /**
     * Report new errors.
     *
     * @since 2.0.0
     *
     * @param string $message Optional email message.
     *
     * @return bool
     */
	public static function submit($message = 'See attached log') {

		$user = wp_get_current_user();

		$last_submit_time = self::get_last_submission_time();

		update_option( self::$last_submit_option_name, time(), $autoload = false );

		$log = array( 'error_log' => self::get_log( $last_submit_time, ARRAY_A ) );

		$environment = new Prompt_Environment();

		$log = array_merge( $log, $environment->to_array() );

		$log_file = wp_tempnam();

		file_put_contents($log_file, json_encode($log));

		$from_address = $user->exists() ? $user->user_email : get_option( 'admin_email' );
		$from_name = $user->exists() ? $user->display_name : '';

		$headers = array(
		    'From: ' . Prompt_Email_Batch::name_address($from_address, $from_name),
        );

		$subject = sprintf(
		    'Error submission from %s',
            html_entity_decode( get_option( 'blogname' ) )
        );

		$sent = wp_mail(Prompt_Core::SUPPORT_EMAIL, $subject, $message, $headers, $log_file );

		unlink($log_file);

		return $sent;
	}

	/**
	 * @since 1.0.0
	 * @param mixed $obj
	 * @return array
	 */
	protected static function object_to_array( $obj ) {
		if ( ! is_object( $obj ) and ! is_array( $obj ) ) {
			return $obj;
		}
		if ( ! is_object( $obj ) ) {
			return array_map( array( __CLASS__, 'object_to_array' ), $obj );
		}
		$object_vars = get_object_vars( $obj );
		if ( $object_vars ) {
			return array_map( array( __CLASS__, 'object_to_array' ), $object_vars );
		}
		if ( method_exists( $obj, 'to_array' ) ) {
			return $obj->to_array();
		}
		$new = array();
		$meta = new ReflectionClass( $obj );
		$methods = $meta->getMethods( ReflectionMethod::IS_PUBLIC );
		foreach ( $methods as $method ) {
			$property_name = substr( $method->name, 4 );
			if ( self::is_getter( $property_name, $meta, $method ) ) {
				$new[$property_name] = $method->invoke( $obj );
			}
		}
		return $new;
	}

	/**
	 * @since 2.0.13
	 * @param string $property_name
	 * @param ReflectionClass $class
	 * @param ReflectionMethod $method
	 * @return bool
	 */
	protected static function is_getter( $property_name, ReflectionClass $class, ReflectionMethod $method ) {
		return $class->hasProperty( $property_name ) and
			'get_' != substr( $method->name, 0, 4 ) and
			$method->getNumberOfParameters() == 0;
	}

}