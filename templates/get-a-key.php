<?php
/**
 * Instructions for obtaining a key.
 *
 * @var string $new_site_url Url for creating a key for this site.
 */
?>
<div class="get-prompt-key">
	<h1><?php _e( 'Welcome to Postmatic', 'Postmatic' ); ?></h1>

	<h2><?php _e( 'Before we get started we need to get you a free Postmatic API key.', 'Postmatic' ); ?></h2>

	<p><?php _e( 'An API key lets our server talk to yours so that Postmatic can do its magic (things like send you new comments, subscriber info, and send mail for you).', 'Postmatic' ); ?></p>

	<p>
		<a href="<?php echo $new_site_url; ?>" target="_blank" class="button button-primary button-large"><?php _e( 'Get a free API key', 'Postmatic' ); ?></a>
	</p>

	<p>
		<a href="https://www.youtube.com/embed/D_8HW3-hEpQ?autoplay=1&TB_iframe=true" class="thickbox button button-large">
			<span><?php _e( '&#9658; Show me how', 'Postmatic' ); ?></span>
		</a>
	</p>

</div>
