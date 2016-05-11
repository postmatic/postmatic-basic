<?php
/**
 * Template variables in scope:
 * @var string    $status    The result of the unsubscribe request
 * @var WP_User  $subscriber
 * @var Prompt_Interface_Subscribable   $site
 */
?>
<div class="padded">
	<p><?php echo $status; ?></p>
	<p>
		<?php
		printf( __( 'To resubscribe go to <a href="%s">%s</a>.', 'Postmatic' ),
			$site->subscription_url(),
			$site->subscription_object_label()
		);
		?>
	</p>
</div>