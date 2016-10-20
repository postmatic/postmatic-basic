<?php
/**
 * Template for the full plugin download modal.
 *
 * @var bool $is_api_transport
 * @var string $upload_url
 */
?>
<div id="download-modal" style="display: none;">

	<?php if ( $is_api_transport ) : ?>

		<div id="download-premium-prompt">
			<?php _e(
				'<h2>It looks like you\'ve upgraded Postmatic but have yet to download the full version!</h2>',
				'Postmatic'
			); ?>

			<a href="<?php echo Prompt_Enum_Urls::DOWNLOAD_PREMIUM; ?>" class="button download"><?php
				_e( 'Download Postmatic', 'Postmatic' )
				?></a>
		</div>

		<p id="install-premium-prompt" style="display: none;">
			<?php
			_e(
				'Great! The full version of Postmatic was just downloaded to your computer. Now we\'ll take you to the plugin install screen. <strong>Just upload and activate as you would for any WordPress plugin.</strong>',
				'Postmatic'
			);
			?>
			<a href="<?php echo $upload_url; ?>" class="button install"><?php
				_e( 'Upload and activate', 'Postmatic' )
				?></a>
		</p>

	<?php elseif ( !$is_api_transport ) : ?>

		<div id="download-labs-prompt">
			<?php _e( 'Big changes are coming to Postmatic Basic.', 'Postmatic' ); ?>
			<?php _e( 'Beginning December 1st, 2016 Postmatic Basic will no longer delivery new posts by email. All of the <em>comment</em> subscriptions (and replies) will continue to be the same, but if you want to continue sending new <em>posts</em> via email you\'ll need to switch to using <strong>Postmatic Labs</strong></a>. (it\'s free) ', 'Postmatic' ); ?><br/>
			<h2>Why the change?</h2>
			<?php _e( 'We\'re going to focus on making Postmatic Basic even simpler, with a single focus on commenting. Since you have already built your flow around sending posts via Postmatic Basic, we want to honor that. We\'ll move that functionality into Labs, where it will continue to be maintained indefinitely.', 'Postmatic' ); ?>			
			<h4>Other cool stuff in Postmatic Labs:</h4>

			<dl class="labslist">
				<dt>Webhook Notifications</dt>
				<dd>Send Postmatic subscribers to 500+ other applications and services. Things like Mailchimp, Google
					Sheets, Infusionsoft, Salesforce and Drip.
				</dd>
			</dl>
			<dl class="labslist">
				<dt>Comment Digests</dt>
				<dd>Chatty users? Let them get a daily digest of new comments instead of each as a new email.</dd>
			</dl>
			<dl class="labslist">
				<dt>Subscriber Growth Widget</dt>
				<dd>Keep up to speed with list performance via a handy new widget on your WordPress dashboard.</dd>
			</dl>
			<dl class="labslist">
				<dt>Postmatic Invitations</dt>
				<dd>Send customized invitations to past commenters and turn them into subscribers.</dd>
			</dl>
			
			<h3>You have until December 1st to upgrade for free to Postmatic labs. You can do it right now in a few clicks:</h3>

			<a href="<?php echo Prompt_Enum_Urls::DOWNLOAD_PREMIUM; ?>" class="button download"><?php
				_e( 'Download Postmatic Labs', 'Postmatic' )
				?></a>
		</div>
		
		<p id="install-labs-prompt" style="display: none;">
			<?php
			_e(
				'Great choice! Postmatic Labs was just downloaded to your computer. Now we\'ll take you to the plugin install screen. <strong>Just upload and activate as you would for any WordPress plugin.</strong>',
				'Postmatic'
			);
			?>
			<a href="<?php echo $upload_url; ?>" class="button install"><?php
				_e( 'Upload and activate', 'Postmatic' )
				?></a>
		</p>

		<a id="dismiss-download-modal"><?php
			_e( 'No thanks', 'Postmatic' )
			?></a>
		
	<?php endif; ?>

</div>
