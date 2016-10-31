<?php
/**
 * Template variables in scope:
 * @var Prompt_Interface_Subscribable   $list        The opted-in list
 */
?>
<div class="padded">
	<?php if ( ! $list ) : ?>
		<p>
			<?php
			_e(
				'We tried to subscribe you, but there was some required information missing from this request.',
				'Postmatic'
			);
			?>
		</p>
	<?php else : ?>
		<p>
			<?php
			printf(
			/* translators: %1$s list URL, %2$s list label */
				__( 'Thanks! You\'re subscribed to <a href="%1$s">%2$s</a>.', 'Postmatic' ),
				$list->subscription_url(),
				$list->subscription_object_label()
			);
			?>
		</p>
	<?php endif; ?>
	<p>
		<a href="<?php echo home_url(); ?>"><?php bloginfo( 'name' ); ?></a>
	</p>
</div>