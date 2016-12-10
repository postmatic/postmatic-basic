<?php
/**
 * Promote all the things!
 *
 * @var bool $is_trial_available
 * @var bool $is_trial_underway
 * @var bool $is_premium
 * @var bool $is_key_present
 */
?>
<div id="core-options-promo">
	<?php if ( $is_trial_available ) : ?>
		<h2><?php _e( '<span>Email was never meant to be a one-way street.</span>', 'Postmatic' ); ?></h2>
		<p>Have you ever received a comment notification and just wanted to hit reply? No forms, no browser, just email?<br />
		<em>We bet your readers have, too.</em>.</p>
		<p>Have better conversations, improved SEO, and support a fantastic WordPress startup at the same time.<br />Enable two-way comment notifications for just $2.99 a month. The first month is free.</p>
		<p><a href="<?php echo admin_url( 'options-general.php?page=postmatic-pricing&trial=true' ); ?>" class="btn-regular btn-postmatic">Upgrade</a></p>
	<?php elseif ( $is_trial_underway ) : ?>
		<h2>You're On Trial</h2>
	<?php elseif ( $is_premium and ! $is_key_present ) : ?>
		<h2>Service is Pending</h2>
	<?php else : ?>
		<h2>You Go Girl</h2>
	<?php endif; ?>
</div>
 