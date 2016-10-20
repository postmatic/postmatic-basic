<?php
/**
 * Notice displayed when Postmatic Labs is required to keep current features.
 *
 * @var string $options_page_url
 * @var string $dismiss_url
 */
?>
<div class="error postmatic-activate-account-notice">
	<p>
		<?php
		_e( '<strong style="color:red">Important:</strong> Postmatic Basic will no longer email your posts beginning December 1st. We\'ve got you covered, but you\'ll need to switch to another plugin.', 'Postmatic' );
		?>
		<a style="margin-top: 15px;" href="<?php echo $options_page_url; ?>" class="button button-primary">
			<strong><?php _e( 'Tell me what I need to do', 'Postmatic' ); ?></strong>
		</a>
	</p>
</div>