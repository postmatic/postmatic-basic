<?php
/**
 * comment flood notification email
 *
 * @var {WP_User} $subscriber
 * @var Prompt_Post $post
 */
?>

<h1>
<?php
	printf(
		__( 'Heads up: the conversation around %s is heating up.', 'Postmatic' ),
		get_the_title( $post->id() )
	)
?>
</h1>

<p>
<?php
_e(
	'You love email. But maybe not this much. We\'re going to pause notifications for you to prevent a flood in your inbox. You will no longer receive new comments on this post.',
	'Postmatic'
);
?>
</p>

<p>
	<?php
	/* translators: %1$s and %3$s may be replaced with link tags, %2$s is the rejoin command */
	printf(
		__(
			'You won\'t receive new comments on this post, unless you reply to this email with the word %1$s\'%2$s\'%3$s. We\'ll send you a recap and renew your subscription.',
			'Postmatic'
		),
		'',
		Prompt_Rejoin_Matcher::target(),
		''
	);
	?>
</p>

<p>
<?php _e( 'View this post online', 'Postmatic' ); ?> at <?php echo get_permalink( $post->id() ); ?>
</p>
