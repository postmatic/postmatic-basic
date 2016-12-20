<?php
/**
* Template variables in scope:
* @var WP_User  $subscriber
* @var Prompt_Interface_Subscribable   $object         The thing being subscribed to
*/
?>
<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">
	<h1 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 36px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;"><?php _e( 'You have unsubscribed', 'Postmatic' ); ?></h1>
<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
	<?php
	printf(
		__( "You'll no longer receive email notices for %s.", 'Postmatic' ),
		$object->subscription_object_label()
	);
	?>
</p>
<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;"><?php _e( 'To re-subscribe visit:', 'Postmatic' ); ?> <?php echo $object->subscription_url(); ?></p>
</div>