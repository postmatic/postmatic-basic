<?php
/**
 * Template variables in scope:
 * @var bool                            $is_valid    Whether the request is valid
 * @var Prompt_User                     $user        The unsubscribed user for valid requests
 * @var Prompt_Interface_Subscribable   $list        The unsubscribed list, null for all lists
 */
?>
<div class="padded">
	<?php if ( ! $is_valid ) : ?>
		<p>
			<?php
			_e(
				'We tried to unsubscribe you, but there was some required information missing from this request.',
				'Postmatic'
			);
			?>
		</p>
	<?php elseif ( ! $list ) : ?>
		<p>
			<?php
			sprintf(
				__( 'Got it. %s has been unsubscribed from all future mailings.', 'Postmatic' ),
				$user->get_wp_user()->user_email
			);
			?>
		</p>
	<?php else : ?>
		<p>
			<?php
			sprintf(
				/* translators: %1$s is email, %2$s list URL, %3$s list label */
				__( 'Got it. %1$s has been unsubscribed from <a href="%2$s">%3$s</a>.', 'Postmatic' ),
				$user->get_wp_user()->user_email,
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