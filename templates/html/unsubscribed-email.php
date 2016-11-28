<?php
/**
* Template variables in scope:
* @var WP_User  $subscriber
* @var Prompt_Interface_Subscribable   $object         The thing being subscribed to
*/
?>
<div class="padded">
	<h1><?php _e( 'You have unsubscribed', 'Postmatic' ); ?></h1>
<p>
	<?php
	printf(
		__( "You'll no longer receive email notices for %s.", 'Postmatic' ),
		$object->subscription_object_label()
	);
	?>
</p>
<p><?php _e( 'To re-subscribe visit:', 'Postmatic' ); ?> <?php echo $object->subscription_url(); ?></p>
</div>