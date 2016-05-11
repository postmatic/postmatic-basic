<?php
/**
 * @var Prompt_Subscribe_Widget $widget
 * @var array $instance
 */
?>
<p>
	<label for="<?php echo $widget->get_field_id( 'title' ); ?>"
		 title="<?php _e( 'Widget heading, leave blank to omit.', 'Postmatic' ); ?>">
		 <?php _e( 'Title:', 'Postmatic' ); ?>
		<span class="help-tip">?</span>
		<input class="widefat"
			 id="<?php echo $widget->get_field_id( 'title' ); ?>"
			 name="<?php echo $widget->get_field_name( 'title' ); ?>"
			 type="text"
			 value="<?php echo $widget->get_default_value( $instance, 'title' ); ?>" />
	</label>
	<label for="<?php echo $widget->get_field_id( 'subscribe_prompt' ); ?>"
		 title="<?php _e( 'Custom message above subscription fields, leave blank for default.', 'Postmatic' ); ?>">
		 <?php _e( 'Description Text:', 'Postmatic' ); ?>
		<span class="help-tip">?</span>
		<input class="widefat"
			 id="<?php echo $widget->get_field_id( 'subscribe_prompt' ); ?>"
			 name="<?php echo $widget->get_field_name( 'subscribe_prompt' ); ?>"
			 type="text"
			 value="<?php echo $widget->get_default_value( $instance, 'subscribe_prompt' ); ?>" />
	</label>
	<label for="<?php echo $widget->get_field_id( 'collect_name' ); ?>">
		<input class="widefat"
			 id="<?php echo $widget->get_field_id( 'collect_name' ); ?>"
			 name="<?php echo $widget->get_field_name( 'collect_name' ); ?>"
			 type="checkbox"
			 <?php checked( $widget->get_default_value( $instance, 'collect_name', true ) ); ?>
			 value="true" />
		<?php _e( 'Collect name (in addition to email)', 'Postmatic' ); ?>
	</label>
</p>