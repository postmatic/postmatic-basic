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

	<?php if ( $is_trial_available and ! $is_premium ) : ?>
	<div id="core-options-promo" class="goupgrade">
		<h2><?php _e( '<span>Email was never meant to be a one-way street.</span>', 'Postmatic' ); ?></h2>
		<p>Have you ever received a comment notification and just wanted to hit reply? No forms, no browser, just email?<br />
		<em>We bet your readers have, too.</em>.</p>
		<p>Have better conversations, improved SEO, and support a fantastic WordPress startup at the same time.<br />Enable two-way comment notifications for just $2.99 a month. The first month is free.</p>
		<p><a href="<?php echo admin_url( 'options-general.php?page=postmatic-pricing&trial=true' ); ?>" class="btn-regular btn-postmatic">Upgrade</a></p>
	</div>
	<?php elseif ( $is_trial_underway ) : ?>
	<div id="core-options-promo-ontrial" class="passive">
		<h3>Welcome back. How is your trial going?</h3>
		<p>We hope you are enjoying your trial of Replyable. Be sure to try out the great features such as replying directly to comment notifications, author tools, and email-based moderation.</p>
		<p>Need something? <a href="#">Get in touch with support.</a></p>
	</div>
	<?php elseif ( $is_premium and ! $is_key_present ) : ?>
	<div id="core-options-promo-pending" class="passive">
		<h3>We're cooking up your service</h3>
		<p>Thanks for upgrading Replyable. We notice you have started premium service but service hasn't been started just yet. <strong>Please reload this page to try to move things along.</strong></p>
		<p>Stuck? <a href="#">Get in touch with support.</a></p>
	</div>
	<?php else : ?>
	<div id="core-options-promo-paid">
		<h3>Thanks for supporting Replyable</h3>
		<p>Your contribution to Replyable will keep the features rolling and make WordPress better for everyone. Cheers to you!</p>
		<p>Need anything? <a href="#">Get in touch with support. You'll be first in line.</a></p>
	</div>
	<?php endif; ?>
 