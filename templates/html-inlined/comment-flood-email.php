<?php
/**
 * comment flood notification email
 *
 * @var Prompt_Post $post
 * @var bool $is_api_delivery
 */
?>

<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">
	<h3 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 22px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;">
		<?php
		printf(
			__( 'Heads up: the conversation around %s is heating up.', 'Postmatic' ),
			html( 'a',
				array( 'href' => get_permalink( $post->id() ) ),
				get_the_title( $post->id() )
			)
		);
		?>
	</h3>

	<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
		<?php
		_e(
			'You love email -- but maybe not this much. We\'re going to pause notifications for you to prevent a flood in your inbox.',
			'Postmatic'
		);
		?>
	</p>

	<?php if ( $is_api_delivery ) : ?>
		<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
			<?php
			/* translators: %1$s and %3$s may be replaced with link tags, %2$s is the rejoin command */
			printf(
				__(
					'You won\'t receive new comments on this post, unless you reply to this email with the word %1$s\'%2$s\'%3$s. We\'ll send you a recap and renew your subscription.',
					'Postmatic'
				),
				sprintf(
					'<a href="mailto:{{{reply_to}}}?subject=%s&body=%s">',
					rawurlencode( __( 'Just press send', 'Postmatic' ) ),
					rawurlencode( Prompt_Rejoin_Matcher::target() )
				),
				Prompt_Rejoin_Matcher::target(),
				''
			);
			?>
		</p>
	<?php endif; ?>

	<p id="button" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal; clear: both; margin-top: 25px;">
		<a href="<?php get_permalink( $post->id() ); ?>" class="btn-secondary" style="margin: 0; padding: 5px; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 2; color: #FFF; text-decoration: none; background-color: #aaa; margin-top: 10px; border-width: 5px 10px; font-weight: normal; margin-right: 10px; text-align: center; cursor: pointer; display: inline-block; border-radius: 15px; border: solid #aaa;"><?php _e( 'Continue the conversation online', 'Postmatic' ); ?></a>
	</p>
</div>
