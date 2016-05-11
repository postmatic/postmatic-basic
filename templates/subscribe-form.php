<?php
/**
 * Variables in scope:
 * @var string               $widget_id         The widget generating this form
 * @var array                $instance          The widget instance data
 * @var Prompt_Interface_Subscribable  $object         Object of subscription
 * @var string               $action            Label for the submit button
 * @var string               $mode             'subscribe' or 'unsubscribe'
 * @var array                $defaults          Default form values
 * @var string               $loading_image_url
 * @var string               $unsubscribe_prompt
 */



?>
<a href="#close"
   class="caldera-modal-closer"
   data-dismiss="modal"
   data-modal="postmatic-widget-popup"
   aria-hidden="true"
   id="postmatic-widget-popup-closer"
   style="display: none;"> </a>
   
<form class="prompt-subscribe" method="post">

	<div class="loading-indicator" style=""></div>

	<p class="message"></p>

	<div class="subscribe primary prompt active"></div>

	<div class="inputs active">
		<input id="<?php echo $widget_id; ?>-nonce" name="subscribe_nonce" type="hidden" />

		<input id="<?php echo $widget_id; ?>-action" name="action" type="hidden" value="<?php echo Prompt_Subscribing::SUBSCRIBE_ACTION; ?>" />

		<input id="<?php echo $widget_id; ?>-action" name="mode" type="hidden" value="<?php echo $mode; ?>" />

		<?php if ( $object ) : ?>
			<input id="<?php echo $widget_id; ?>-type" name="object_type" type="hidden" value="<?php echo get_class( $object ); ?>" />

			<input id="<?php echo $widget_id; ?>-object-id" name="object_id" type="hidden" value="<?php echo $object->id(); ?>" />
		<?php endif; ?>

		<label class="prompt-topic" for="subscribe_topic">
			<?php _e( 'This field is intentionally empty', 'Postmatic' ); ?> *
			<input id="<?php echo $widget_id; ?>-topic" name="subscribe_topic" type="text" value="" />
		</label>

		<?php if ( 'unsubscribe' == $mode ) : ?>
			<div class="unsubscribe prompt">
				<?php printf( __( 'You are already subscribed to %s.', 'Postmatic' ), $object->subscription_object_label() ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! is_user_logged_in() ) : ?>

			<?php if ( $instance['collect_name'] ) : ?>
				<input id="<?php echo $widget_id; ?>-name"
					   name="subscribe_name"
					   type="text"
					   class="prompt-subscribe-name"
					   placeholder="<?php _e( 'Name (optional)', 'Postmatic' ); ?>"
					   value="<?php echo esc_attr( $defaults['subscribe_name'] ); ?>" />
			<?php endif; ?>

			<input id="<?php echo $widget_id; ?>-email"
				   name="subscribe_email"
				   type="text"
				   class="prompt-subscribe-email"
				   placeholder="<?php _e( 'Email', 'Postmatic' ); ?>"
				   value="<?php echo esc_attr( $defaults['subscribe_email'] ); ?>" />

		<?php endif; ?>

		<input id="<?php echo $widget_id; ?>-submit"
			   name="subscribe_submit"
			   class="submit"
			   style="font-size: 90%; text-transform: capitalize;"
			   type="submit"
			   value="<?php echo $action; ?>" />

	</div>

</form>
