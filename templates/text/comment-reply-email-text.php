<?php
/**
 * comment notification email template
 * variables in scope:
 * @var WP_User $comment_author
 * @var string $commenter_name
 * @var string $comment_text
 * @var Prompt_Post $subscribed_post
 * @var string $subscribed_post_author_name
 * @var array $previous_comments
 * @var WP_User $parent_author
 * @var string $parent_author_name
 * @var object $parent_comment
 * @var bool $is_api_delivery
 */
?>
{{subscriber_comment_intro_text}}

<?php echo $comment_text; ?>

<?php if ( $is_api_delivery ) : ?>
	<p>
		<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>
	</p>
<?php endif; ?>


<h2><?php _e( 'Here\'s a recap of this post and conversation:', 'Postmatic' ); ?></h2>

<p>
<?php
/* translators: %1$s is post title, %2$s date, %3$s time, %4$s author */
printf(
	__( '%1$s was published on %2$s by %4$s.' ),
	get_the_title( $subscribed_post->id() ),
	get_the_date( '', $subscribed_post->get_wp_post() ),
	get_the_time( '', $subscribed_post->get_wp_post() ),
	$subscribed_post_author_name
);
?>
</p>

<div>
<?php echo Prompt_Formatting::escape_handlebars_expressions( $subscribed_post->get_excerpt() ); ?>
</div>

<p>
<?php
printf(
	__( 'There were %d comments previous to this. Here is this reply in context:', 'Postmatic' ),
	wp_count_comments( $subscribed_post->id() )->approved
);
?>
</p>

<div>
<?php
wp_list_comments( array(
	'callback' => array( 'Prompt_Email_Comment_Rendering', 'render_text' ),
	'end-callback' => '__return_empty_string',
	'style' => 'div',
), $previous_comments );
?>
</div>

<?php if ( $is_api_delivery ) : ?>
	<p>
	<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>
	</p>

	<p>
	<?php
	printf(
		__(
			"To no longer receive other comments or replies in this discussion reply with the word '%s'.",
			'Postmatic'
		),
		Prompt_Unsubscribe_Matcher::target()
	);
	?>
	</p>
<?php endif; ?>
