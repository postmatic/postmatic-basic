<?php
/**
 * HTML post notification email template
 *
 * Post globals are set so template tags like the_title() and the_content() will work.
 *
 * @see prompt/post_email/template_data
 *
 * @var bool $excerpt_only
 * @var string $after_title_content
 * @var string $by author credit
 * @var Prompt_Interface_Subscribable $subscribed_object
 * @var bool $is_api_delivery
 * @var string $after_post_content
 * @var array $comments Comments so far for post subscriptions
 */
?>


<div class="padded postmatic-header">
	<h1 id="the_title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		<small><?php echo $by ?></small>
	</h1>
</div>

<?php echo $after_title_content; ?>

<div class="padded">
	<div id="the_content" class="postmatic-content">
		<?php $excerpt_only ? the_excerpt() : the_content(); ?>
	</div>


	<?php if ( $excerpt_only ) : ?>
		<p id="button"><a href="<?php the_permalink(); ?>"
		                  class="btn-secondary"><?php _e( 'View this post online', 'Postmatic' ); ?></a></p>
	<?php endif; ?>

	<?php echo $after_post_content; ?>

</div>
<?php if ( comments_open() and !$excerpt_only ) : ?>

<?php if ( !empty( $comments ) ) : ?>

	<div class="previous-comments" id="comments">
		<h3 class="comment-count">
			<?php
			printf(
				_n(
					'There is <a href="%1$s">one comment</a>',
					'There are <a href="%1$s">%2$s comments</a>',
					count( $comments ),
					'Postmatic'
				),
				get_permalink() . '#comments',
				number_format_i18n( count( $comments ) )
			);
			?>
		</h3>
		<?php
		wp_list_comments( array(
			'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
			'style' => 'div',
		), $comments );
		?>
	</div>
<?php endif; ?>

<div class="utils">
	<div class="reply-prompt">
		<a href="mailto:{{{reply_to}}}?subject=<?php echo rawurlencode( __( 'A comment...', 'Postmatic' ) ); ?>">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30"
			     align="left" style="float: left; margin-right: 10px;"/>
		</a>
		<h3 class="reply">
			<a href="mailto:{{{reply_to}}}?subject=<?php echo rawurlencode( __( 'A comment...', 'Postmatic' ) ); ?>">
				<?php _e( 'Reply to this email to add a comment. Your email address will not be shown.', 'Postmatic' ); ?>
			</a>
			<br/>
			<small>
				<?php
				printf(
					__(
						'You\'re invited to comment on this post by replying to this email. If you do, it may be published immediately or held for moderation, depending on the comment policy of %s.',
						'Postmatic'
					),
					get_bloginfo( 'name' )
				);
				?>
				<br/>
				&raquo; <a href="<?php the_permalink(); ?>"><?php _e( 'View this post online', 'Postmatic' ); ?></a>
			</small>
		</h3>
		<?php endif; ?>
	</div>
</div>

