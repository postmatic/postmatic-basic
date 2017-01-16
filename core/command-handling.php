<?php

/**
 * Manage encoding and decoding of commands as message metadata.
 * @since 1.0.0
 */
class Prompt_Command_Handling {

	/**
	 * @var array Default metadata IDs to command class mapping
	 */
	protected static $class_map = array(
		1 => 'Prompt_Comment_Command',
		2 => 'Prompt_Register_Subscribe_Command',
		3 => 'Prompt_New_Post_Comment_Command',
		4 => 'Postmatic\Commentium\Commands\Comment_Moderation',
		5 => 'Prompt_Comment_Flood_Command',
		6 => 'Prompt_Confirmation_Command',
		7 => 'Postmatic\Premium\Commands\Forward',
		8 => 'Prompt_Post_Reply_Command',
		9 => 'Postmatic\Premium\Commands\Post_Request',
	);

	/**
	 * Get the class associated with an encoded ID.
	 * @since 2.0.0
	 * @param int $id
	 * @return null|string class name or null if not found
	 */
	public static function get_class( $id ) {

		if ( ! isset( self::$class_map[$id] ) ) {
			return null;
		}

		return apply_filters( 'prompt/command_handling/get_class', self::$class_map[$id], $id );
	}

	/**
	 * Get the encoded ID for a class.
	 * @since 2.0.0
	 * @param string $class
	 * @return null|int class ID or null if not found
	 */
	public static function get_class_id( $class ) {

		$class_to_ids = array_flip( self::$class_map );

		if ( isset( $class_to_ids[$class] ) ) {
			return $class_to_ids[$class];
		}

		// Map subclasses to their parent class ID
		foreach( $class_to_ids as $our_class => $class_id ) {
			if ( is_subclass_of( $class, $our_class ) ) {
				return $class_id;
			}
		}

		return null;
	}

	/**
	 * Create a command from message data.
	 * @param object $update Message data in prompt format.
	 * @return Prompt_Interface_Command
	 */
	public static function make_command( $update ) {

		$metadata = $update->metadata;

		if ( !isset( $metadata->ids ) )
			return null;

		$data = $metadata->ids;

		$class_id = array_shift( $data );

		$class = self::get_class( $class_id );

		if ( ! $class ) {
			Prompt_Logging::add_error(
				'invalid_command_id',
				__( 'Received a reply with invalid command data.', 'Postmatic' ),
				$update
			);
			return null;
		}

		/** @var Prompt_Interface_Command $command */
		$command = new $class;
		$command->set_keys( $data );
		$command->set_message( $update );

		return $command;
	}

	/**
	 * Get the metadata required to reproduce a command instance.
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Interface_Command $command
	 * @return stdClass
	 */
	public static function get_command_metadata( Prompt_Interface_Command $command ) {

		$metadata = new stdClass();

		$class = get_class( $command );

		$class_id = self::get_class_id( $class );

		if ( is_null( $class_id ) ) {
			Prompt_Logging::add_error(
				'invalid_command_id',
				__( 'Tried to create an email with an unrecognized reply command.', 'Postmatic' ),
				compact( 'command', 'email' )
			);
			return $metadata;
		}

		$data = $command->get_keys();

		array_unshift( $data, $class_id );

		$metadata->ids = $data;

		return $metadata;
	}

	/**
	 * Shorthand method to get metadata for a comment command.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @param int $parent_comment_id
	 * @return stdClass
	 */
	public static function get_comment_command_metadata( $user_id, $post_id, $parent_comment_id = null ) {
		$command = new Prompt_Comment_Command();
		$command->set_user_id( $user_id );
		$command->set_post_id( $post_id );
		if ( $parent_comment_id ) {
			$command->set_parent_comment_id( $parent_comment_id );
		}
		return self::get_command_metadata( $command );
	}

	/**
	 * Generate comment reply address macros from comments and the replying user ID.
	 *
	 * @since 2.0.0
	 *
	 * @param array $comments
	 * @param int $replier_id
	 * @return array
	 */
	public static function get_comment_reply_macros( array $comments, $replier_id ) {

		$macros = array();
		foreach ( $comments as $comment ) {
			$macros['reply_to_comment_'. $comment->comment_ID] = Prompt_Email_Batch::trackable_address(
				self::get_comment_command_metadata( $replier_id, $comment->comment_post_ID, $comment->comment_ID )
			);
		}

		return $macros;
	}
}
