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
			<?php _e( 'Want even more Postmatic?', 'Postmatic' ); ?>
			<?php _e( 'Postmatic Labs is our playground for new features and wild ideas.', 'Postmatic' ); ?><br/>
			<?php _e(
				'Want to see what\'s in the pipeline? Download labs for free and help out by sharing feedback and bug reports.',
				'Postmatic'
			); ?>

			<h3>Right now in Postmatic Labs:</h3>

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
