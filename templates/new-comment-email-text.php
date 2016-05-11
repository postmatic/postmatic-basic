<?php
/**
 *
 * comment notification email template
 * variables in scope:
 * @var {WP_User}           $comment_author
 * @var string $commenter_name
 * @var object $comment
 * @var Prompt_Post $subscribed_post
 * @var array $previous_comments
 * @var bool $is_api_delivery
 */
?>
<h2>
	<?php
	printf(
		__( '%s added a comment on %s', 'Postmatic' ),
		$commenter_name,
		get_the_title( $comment->comment_post_ID )
	);
	?>
</h2>

<div>
	<?php echo $comment->comment_content; ?>
</div>

<?php if ( count( $previous_comments ) > 1 ) : ?>
	<p>
		<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>
	</p>
	<p>
		<?php
		printf(
			__(
				'You\'re invited to respond by replying to this email. If you do, it may be published immediately or held for moderation, depending on the comment policy of %s.',
				'Postmatic'
			),
			get_the_title( $comment->comment_post_ID )
		);
		?>
	</p>

	<?php if ( $is_api_delivery ) : ?>
		<h2><?php _e( 'Recently in this conversation...', 'Postmatic' ); ?></h2>

		<div id="comments">
			<?php
			wp_list_comments( array(
				'callback' => array( 'Prompt_Email_Comment_Rendering', 'render_text' ),
				'end-callback' => '__return_empty_string',
				'style' => 'div',
			), $previous_comments );
			?>
		</div>
	<?php endif; ?>

<?php endif; ?>

<p>
	<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>
</p>

<p>
	<?php
	printf(
		__(
			'Please note: Your reply will be published publicly and immediately on %s.',
			'Postmatic'
		),
		get_the_title( $comment->comment_post_ID )
	);
	?>
</p>

<p>
	<?php
	printf(
		__(
			"To no longer receive other comments or replies in this discussion reply with the word '%s'.",
			'Postmatic'
		),
		Prompt_Unsubscribe_Matcher::target()
	)
	?>
</p>
