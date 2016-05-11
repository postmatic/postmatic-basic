<?php

/**
 * Interface for lists: things that can be subscribed to.
 *
 * @since 2.0.0 Added named language phrases
 * @since 1.0.0
 *
 */
interface Prompt_Interface_Subscribable extends Prompt_Interface_Identifiable {

	/**
	 * Get the user IDs of all subscribers to this object.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function subscriber_ids();

	/**
	 * Determine whether a user is subscribed to this object.
	 *
	 * @since 1.0.0
	 *
	 * @param $user_id
	 * @return mixed
	 */
	function is_subscribed( $user_id );

	/**
	 * Ensure that a user is subscribed to this object.
	 *
	 * Do nothing if the user is already subscribed.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id
	 * @return Prompt_Interface_Subscribable A reference to this object.
	 */
	function subscribe( $user_id );

	/**
	 * Unsubscribe a user from this object.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id
	 * @return Prompt_Interface_Subscribable A reference to this object.
	 */
	function unsubscribe( $user_id );

	/**
	 * An URL representing the list.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function subscription_url();

	/**
	 * A title.
	 *
	 * @since 2.0.0 Added format parameter
	 * @since 1.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	function subscription_object_label( $format = Prompt_Enum_Content_Types::HTML );

	/**
	 * A confirmation message.
	 *
	 * @since 2.0.0 Added format parameter
	 * @since 1.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	function subscription_description( $format = Prompt_Enum_Content_Types::HTML );

	/**
	 * A prompt phrase to select this list in an email reply.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	function select_reply_prompt( $format = Prompt_Enum_Content_Types::HTML );

	/**
	 * A phrase for a toggle-style interface inviting a user to join this list.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format 'html' or 'text', default 'html'.
	 * @return string
	 */
	function subscribe_prompt( $format = Prompt_Enum_Content_Types::HTML );

	/**
	 * A phrase that can be used to identify this list in a reply command.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	function subscribe_phrase();

	/**
	 * Whether given text matches the subscribe phrase.
	 *
	 * @since 2.0.0
	 *
	 * @param string $text
	 * @return string
	 */
	function matches_subscribe_phrase( $text );

	/**
	 * Get the IDs of all objects a user is subscribed to.
	 *
	 * @since 1.0.0
	 *
	 * @param $user_id
	 * @return array
	 */
	static function subscribed_object_ids( $user_id );

	/**
	 * Get the IDs of all users with subscriptions to this type of object.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	static function all_subscriber_ids();
}