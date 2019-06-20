<?php
/**
 * Template variables:
 *
 * @var string $account_html The Freemius account HTML.
 */
?>
<style>
h2#prompt-settings-header {
	background: url(<?php echo esc_url( Prompt_Core::$url_path ); ?>/media/replyable.png) no-repeat top left;
	background-size: 250px;
	height: 65px;
	margin-bottom: 10px;
	margin-top: 30px;
	text-align: center;
}
.wrap {
	padding: 1% 5% 2% 2%;
	width: auto;
	margin: 0;
}

h2#prompt-settings-header span {
	display: none; }
</style>
<div class="wrap">
	<h2 id="prompt-settings-header"><span>Replyable</span></h2>


	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=postmatic&tab=prompt-tab-content-core' ) ); ?>" id="prompt-tab-core" class="nav-tab show nav-tab-active" data-tab-name="prompt-tab-content-core" style=""><?php esc_html_e( 'Get Started', 'postmatic' ); ?></a><a href="<?php echo esc_url( admin_url( 'options-general.php?page=postmatic&tab=prompt-tab-content-configure-your-template' ) ); ?>" id="prompt-tab-configure-your-template" class="nav-tab" data-tab-name="prompt-tab-content-configure-your-template" style=""><?php esc_html_e( 'Configure Your Template', 'postmatic' ); ?></a><a href="<?php echo esc_url( admin_url( 'options-general.php?page=postmatic&tab=prompt-tab-content-comment-delivery' ) ); ?>" id="prompt-tab-comment-delivery" class="nav-tab" data-tab-name="prompt-tab-content-comment-delivery" style=""><?php esc_html_e( 'Comment Subscription Options', 'postmatic' ); ?></a><a href="<?php echo esc_url( admin_url( 'options-general.php?page=postmatic&tab=prompt-tab-content-import-subscribe-reloaded' ) ); ?>" id="prompt-tab-import-subscribe-reloaded" class="nav-tab" data-tab-name="prompt-tab-content-import-subscribe-reloaded" style=""><?php echo esc_html_e( 'Importer', 'postmatic' ); ?></a><a href="<?php echo esc_url( admin_url( 'options-general.php?page=postmatic&tab=prompt-tab-content-recommended-plugins' ) ); ?>" id="prompt-tab-recommended-plugins" class="nav-tab" data-tab-name="prompt-tab-content-recommended-plugins" style=""><?php esc_html_e( 'Recommended Plugins', 'postmatic' ); ?></a></h2>
<?php echo $account_html; ?>
</div>
