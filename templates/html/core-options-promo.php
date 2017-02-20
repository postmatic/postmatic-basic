<?php
/**
 * Promote all the things!
 *
 * @var bool $is_pending_activation
 * @var bool $is_trial_available
 * @var bool $is_trial_underway
 * @var bool $is_paying
 * @var bool $is_key_present
 * @var bool $is_api_transport
 * @var bool $has_changed_licenses
 */
?>

<?php if ( $is_pending_activation ) : ?>
	<div id="core-options-promo-ontrial" class="passive alert">
		<h3><?php _e( 'Hey! An important message about your account!', 'Postmatic' ); ?></h3>
		<p><?php _e(
				'It looks like you have upgraded Replyable but have not yet verified your email address. This step is required to begin service. Please check your inbox for an activation email in order to continue.',
				'Postmatic'
			); ?>
		</p>
		<p><?php printf(
				__( 'Need something? <a href="%s">Get in touch with support.</a>', 'Postmatic' ),
				admin_url( 'options-general.php?page=postmatic-contact' )
			); ?>
		</p>
	</div>
<?php elseif ( ! $is_trial_underway and ! $is_paying and $is_key_present and ! $is_api_transport and $has_changed_licenses ) : ?>
    	<div id="core-options-promo" class="goupgrade">
		<h2><span><?php _e( 'Email was never meant to be a one-way street.', 'Postmatic' ); ?></span></h2>
		<p><?php _e(
				'Have you ever received a comment notification and just wanted to hit reply? No forms, no browser, just email?',
				'Postmatic'
			); ?>
		</p>
		<p><?php _e(
				'Have better conversations, improve SEO, and support a fantastic WordPress startup at the same time.',
				'Postmatic'
			); ?>
			<br/><?php _e(
				'Enable two-way comment notifications for just $2.99 a month. The first month is free.',
				'Postmatic'
			); ?>
		</p>
		<p><a href="<?php echo admin_url( 'options-general.php?page=postmatic-pricing' ); ?>"
		      class="btn-regular btn-postmatic"><?php _e( 'Upgrade', 'Postmatic' ); ?></a></p>
	</div>
<?php elseif ( ! $is_trial_underway and ! $is_paying and $is_key_present and $is_api_transport ) : ?>
	<div id="core-options-postmatic-service" class="passive alert">
		<h3><?php _e( 'Hey! An important message about your account!', 'Postmatic' ); ?></h3>
		<p><?php _e(
				'It looks like you used to use Postmatic on this site and that you are still paying for Postmatic service. That\'s no good.',
				'Postmatic'
			); ?>
		</p>
		<p><?php _e(
				'You might want to cancel your Postmatic account and switch to a paid Replyable account instead. Replyable service starts at just $2.99 a month.',
				'Postmatic'
			); ?>
		</p>
		<p><?php printf(
				__( 'Need something? <a href="%s">Get in touch with support.</a>', 'Postmatic' ),
				admin_url( 'options-general.php?page=postmatic-contact' )
			); ?>
		</p>
	</div>
<?php elseif ( ! $is_trial_underway and ! $is_paying and $is_key_present and ! $is_api_transport ) : ?>
	<div id="core-options-promo" class="goupgrade">
		<h2><span><?php _e( 'Email was never meant to be a one-way street.', 'Postmatic' ); ?></span></h2>
		<h3><?php _e( 'Important: It looks like this site previously used Postmatic Basic', 'Postmatic' ); ?></h3>
		<p><?php _e( 'Postmatic Basic is now Replyable. We\'ve added some features and removed a few as well.', 'Postmatic' ); ?></p>
		<p><?php _e(
				'With Replyable you can get all of the awesome commenting features of the full version of Postmatic, but for 15% the cost. Plans start at $2.99/month. Or, stick with the free service and still get awesome comment subscriptions, but without the bells and whistles.',
				'Postmatic'
			); ?>
		</p>
		<p><a href="http://replyable.com/vs"><?php
				_e( 'Find out more about the differences between Postmatic Basic and Replyable.', 'Postmatic' ); ?></a>
		</p>
	</div>
<?php elseif ( $is_trial_available and ! $is_paying ) : ?>
	<div id="core-options-promo" class="goupgrade">
		<h2><span><?php _e( 'Email was never meant to be a one-way street.', 'Postmatic' ); ?></span></h2>
		<p><?php _e(
				'Have you ever received a comment notification and just wanted to hit reply? No forms, no browser, just email?',
				'Postmatic'
			); ?>
		</p>
		<p><?php _e(
				'Have better conversations, improve SEO, and support a fantastic WordPress startup at the same time.',
				'Postmatic'
			); ?>
			<br/><?php _e(
				'Enable two-way comment notifications for just $2.99 a month. The first month is free.',
				'Postmatic'
			); ?>
		</p>
		<p><a href="<?php echo admin_url( 'options-general.php?page=postmatic-pricing&trial=true' ); ?>"
		      class="btn-regular btn-postmatic"><?php _e( 'Upgrade', 'Postmatic' ); ?></a></p>
	</div>
<?php elseif ( $is_trial_underway and $is_key_present ) : ?>
	<div id="core-options-promo-ontrial" class="active">
		<h3><?php _e( 'Welcome back. How is your trial going?', 'Postmatic' ); ?></h3>
		<p><?php _e(
				'We hope you are enjoying your trial of Replyable. Be sure to try out the great features such as replying directly to comment notifications, author tools, and email-based moderation.',
				'Postmatic'
			); ?>
		</p>
		<p><?php printf(
				__( 'Need something? <a href="%s">Get in touch with support.</a>', 'Postmatic' ),
				admin_url( 'options-general.php?page=postmatic-contact' )
			); ?>
		</p>
	</div>
<?php elseif ( ! $is_key_present ) : ?>
	<div id="core-options-promo-pending" class="passive">
		<h3><?php _e( 'We\'re cooking up your service', 'Postmatic' ); ?></h3>
		<p><?php _e(
				'Thanks for upgrading Replyable. We notice you have started premium service but service hasn\'t been started just yet. <strong>Please reload this page to try to move things along.</strong>',
				'Postmatic'
			); ?>
		</p>
		<p><?php printf(
				__( 'Stuck? <a href="%s">Get in touch with support.</a>', 'Postmatic' ),
				admin_url( 'options-general.php?page=postmatic-contact' )
			); ?>
		</p>
	</div>
<?php else : ?>
	<div id="core-options-promo-paid">
		<h3><span><?php _e( 'Thanks for supporting Replyable!', 'Postmatic' ); ?></span></h3>
		<p><?php _e(
				'Your contribution to Replyable will keep the features rolling and make WordPress better for everyone. Cheers to you!',
				'Postmatic'
			); ?>
		</p>
		<p><?php printf(
				__( 'Need anything? <a href="%s">Get in touch with support. You\'ll be first in line.</a>', 'Postmatic' ),
				admin_url( 'options-general.php?page=postmatic-contact' )
			); ?>
		</p>
	</div>
<?php endif; ?>
 