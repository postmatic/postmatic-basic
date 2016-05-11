<?php
/**
 * Notice displayed when Postmatic does not yet have a key.
 *
 * @var string $options_page_url
 */
?>
<div class="notice postmatic-activate-account-notice">
	<p>
		<?php
		_e( '<strong style="color:red">Important:</strong> - You need to complete your Postmatic configuration.', 'Postmatic' );
		?>
			<a target="_blank" style="float:right; margin-top: -5px;" href="<?php echo $options_page_url; ?>" class="button button-primary">
			<strong><?php _e( 'Get an API key and activate Postmatic', 'Postmatic' ); ?></strong>
		</a>
	</p>
</div>