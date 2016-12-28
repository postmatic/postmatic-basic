<?php
/**
 * Template variables in scope:
 * @var Prompt_Interface_Subscribable   $list        The opted-in list
 */
?>
<div class="padded">
	<img src="<?php echo site_icon_url(); ?>" style="width: 32px; margin-right: 5px; display: block; float: left;">
	<h1 style="clear: none; font-weight: normal;"><?php bloginfo( 'name' ); ?> - <?php bloginfo('description'); ?></h1>	<?php if ( ! $list ) : ?>
		<p>
			<?php
			_e(
				'We tried to subscribe you, but somethign went wrong. Try again?',
				'Postmatic'
			);
			?>
		</p>
	<?php else : ?>
		<p>
			<?php
			printf(
			/* translators: %1$s list URL, %2$s list label */
				__( 'Done! You\'re subscribed to <a href="%1$s">%2$s</a>.', 'Postmatic' ),
				$list->subscription_url(),
				$list->subscription_object_label()
			);
			?>
		</p>
	<?php endif; ?>
	<p>
		<a class="button" href="<?php echo home_url(); ?>">&#8592; Back to <?php bloginfo( 'name' ); ?></a>
	</p>
</div>