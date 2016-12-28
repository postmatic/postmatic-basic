<?php
/**
 * comment flood notification email
 *
 * @var Prompt_Post $post
 * @var bool $is_api_delivery
 */
?>

<div class="padded">
	<h3>
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

	<p>
		<?php
		_e(
			'You love email -- but maybe not this much. We\'re going to pause notifications for you to prevent a flood in your inbox.',
			'Postmatic'
		);
		?>
	</p>

	<?php if ( $is_api_delivery ) : ?>
		<p>
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
				'</a>'
			);
			?>
		</p>
	<?php endif; ?>

	<p id="button">
		<a href="<?php get_permalink( $post->id() ); ?>"
	       class="btn-secondary"><?php _e( 'Continue the conversation online', 'Postmatic' ); ?></a>
	</p>
</div>