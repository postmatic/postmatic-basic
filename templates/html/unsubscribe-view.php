<?php
/**
 * Template variables in scope:
 * @var bool                            $is_valid    Whether the request is valid
 * @var Prompt_User                     $user        The unsubscribed user for valid requests
 * @var Prompt_Interface_Subscribable   $list        The unsubscribed list, null for all lists
 */
?>
<div class="padded">
	<img src="<?php echo site_icon_url(); ?>" style="width: 32px; margin-right: 5px; display: block; float: left;">
	<h1 style="clear: none; font-weight: normal;"><?php bloginfo( 'name' ); ?> - <?php bloginfo('description'); ?></h1>
	<?php if ( ! $is_valid ) : ?>
		<p>
			<?php
			_e(
				'We tried to unsubscribe you but something went wrong. Try getting in touch with the site adminstrator.',
				'Postmatic'
			);
			?>
		</p>
	<?php elseif ( ! $list ) : ?>
		<p>
			<?php
			printf(
				__( 'Got it. %s has been unsubscribed from all future mailings.', 'Postmatic' ),
				$user->get_wp_user()->user_email
			);
			?>
		</p>
	<?php else : ?>
		<p>
			<?php
			printf(
				/* translators: %1$s is email, %2$s list URL, %3$s list label */
				__( 'No problem. %1$s has been unsubscribed from <a href="%2$s">%3$s</a>.', 'Postmatic' ),
				$user->get_wp_user()->user_email,
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