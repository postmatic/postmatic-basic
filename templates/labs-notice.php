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
		_e( '<strong style="color:red">Important:</strong> a Postmatic upgrade requires your action.', 'Postmatic' );
		?>
		<a style="float:right; margin-top: -5px; margin-left: 5px;" href="<?php echo $dismiss_url; ?>" class="button">
			<?php _e( 'Dismiss', 'Postmatic' ); ?>
		</a>
		<a style="float:right; margin-top: -5px;" href="<?php echo $options_page_url; ?>" class="button button-primary">
			<strong><?php _e( 'Visit Postmatic settings for the next step', 'Postmatic' ); ?></strong>
		</a>
	</p>
</div>