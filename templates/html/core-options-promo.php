<?php
/**
 * Promote all the things!
 *
 * @var bool $is_trial_available
 * @var bool $is_trial_underway
 * @var bool $is_paying
 * @var bool $is_key_present
 * @var bool $is_api_transport
 */
?>

	<?php if ( ! $is_trial_underway and ! $is_paying and $is_key_present and $is_api_transport ) : ?>
		<div id="core-options-postmatic-service" class="passive alert">
		<h3>Hey! An important message about your account!</h3>
		<p>It looks like you used to use Postmatic on this site and that you are still paying for Postmatic service. That's no good.</p>
		<p>You might want to cancel your Postmatic account and switch to a paid Replyable account instead. Replyable service starts at just $2.99 a month.</p>
		<p>Need something? <a href="options-general.php?page=postmatic-contact">Get in touch with support.</a></p>
		</div>
	<?php elseif ( ! $is_trial_underway and ! $is_paying and $is_key_present and ! $is_api_transport ) : ?>
		<div id="core-options-promo" class="goupgrade">
		<h2><?php _e( '<span>Email was never meant to be a one-way street.</span>', 'Postmatic' ); ?></h2>
		<h3>Important: It looks like this site used to use Postmatic Basic</h3>
		<p>Postmatic Basic is now Replyable. We've added some features and removed a few as well.</p>
		<p>With Replyable you can get all of the awesome commenting features of the full version of Postmatic, but for 15% the cost. Plans start at $2.99/month. Or, stick with the free service and still get awesome comment subscriptions, but without the bells and whistles.</p>
		<p><a href="http://replyable.com/vs">Find out more about the differences between Postmatic Basic and Replyable.</a></p>
		</div>
	<?php elseif ( $is_trial_available and ! $is_paying ) : ?>
	<div id="core-options-promo" class="goupgrade">
		<h2><?php _e( '<span>Email was never meant to be a one-way street.</span>', 'Postmatic' ); ?></h2>
		<p>Have you ever received a comment notification and just wanted to hit reply? No forms, no browser, just email?<br />
		<p>Have better conversations, improve SEO, and support a fantastic WordPress startup at the same time.<br />Enable two-way comment notifications for just $2.99 a month. The first month is free.</p>
		<p><a href="<?php echo admin_url( 'options-general.php?page=postmatic-pricing&trial=true' ); ?>" class="btn-regular btn-postmatic">Upgrade</a></p>
	</div>
	<?php elseif ( $is_trial_underway and $is_key_present ) : ?>
	<div id="core-options-promo-ontrial" class="passive">
		<h3>Welcome back. How is your trial going?</h3>
		<p>We hope you are enjoying your trial of Replyable. Be sure to try out the great features such as replying directly to comment notifications, author tools, and email-based moderation.</p>
		<p>Need something? <a href="#">Get in touch with support.</a></p>
	</div>
	<?php elseif ( ! $is_key_present ) : ?>
	<div id="core-options-promo-pending" class="passive">
		<h3>We're cooking up your service</h3>
		<p>Thanks for upgrading Replyable. We notice you have started premium service but service hasn't been started just yet. <strong>Please reload this page to try to move things along.</strong></p>
		<p>Stuck? <a href="#">Get in touch with support.</a></p>
	</div>
	<?php else : ?>
	<div id="core-options-promo-paid">
		<h3><span>Thanks for supporting Replyable!</span></h3>
		<p>Your contribution to Replyable will keep the features rolling and make WordPress better for everyone. Cheers to you!</p>
		<p>Need anything? <a href="options-general.php?page=postmatic-contact">Get in touch with support. You'll be first in line.</a></p>
	</div>
	<?php endif; ?>
 