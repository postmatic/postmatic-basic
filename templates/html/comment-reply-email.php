<?php
/**
 * comment notification email template
 * variables in scope:
 * @var WP_User     $comment_author
 * @var string      $comment_author_url
 * @var string      $commenter_name
 * @var string      $comment_text
 * @var string      $avatar
 * @var Prompt_Post $subscribed_post
 * @var string      $subscribed_post_author_name
 * @var string      $subscribed_post_title_link
 * @var array       $previous_comments
 * @var WP_User     $parent_author
 * @var string      $parent_author_name
 * @var object      $parent_comment
 * @var bool        $is_api_delivery
 */

?>

<div class="padded">

	<p class="padding">{{{subscriber_comment_intro_html}}}</p>

	<div class="new-reply">
		<div class="primary-comment comment">
			<div class="comment-header">
				<?php echo $avatar; ?>
				<div class="author-name">
					<?php if ( $comment_author_url ) : ?>
						<a href="<?php echo esc_url( $comment_author_url ); ?>">
							<?php echo $commenter_name; ?>
						</a>
					<?php else : ?>
						<?php echo $commenter_name; ?>
					<?php endif; ?>
				</div>
				<div class="comment-body">
					<?php echo $comment_text; ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $is_api_delivery ) : ?>
		<div class="reply-prompt">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;"/>

			<h3 class="reply">
				<a href="mailto:{{{reply_to}}}?subject=<?php
				echo rawurlencode( sprintf( __( 'In reply to %s', 'Postmatic' ), $commenter_name ) );
				?>"><?php printf( __( 'Reply to this email to respond to %s.', 'Postmatic' ), $commenter_name ); ?>
				</a>
			</h3>
		</div>
	<?php endif; ?>
</div>


<div class="padded gray">
	<div class="context">
		<h3><?php _e( 'Here\'s a recap of this post and conversation:', 'Postmatic' ); ?></h3>

		<p>
			<?php
			/* translators: %1$s is post title, %2$s date, %3$s time, %4$s author */
			printf(
				__( '%1$s was published on %2$s by %4$s.' ),
				$subscribed_post_title_link,
				get_the_date( '', $subscribed_post->get_wp_post() ),
				get_the_time( '', $subscribed_post->get_wp_post() ),
				$subscribed_post_author_name
			);
			?>
		</p>
		<?php echo get_the_post_thumbnail( $subscribed_post->id(), 'medium' ); ?>
		<p class="excerpt"><?php echo Prompt_Formatting::escape_handlebars_expressions( $subscribed_post->get_excerpt() ); ?></p>
	</div>

	<h3 class="summary">
		<?php
		printf(
			__( 'There were <a href="%1$s">%2$d comments</a> previous to this. Here is this reply in context:', 'Postmatic' ),
			get_permalink( $subscribed_post->id() ) . '#comments',
			wp_count_comments( $subscribed_post->id() )->approved
		);
		?>
	</h3>

	<div class="previous-comments" id="comments">
		<?php
		wp_list_comments( array(
			'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
			'style' => 'div',
		), $previous_comments );
		?>
	</div>

	<?php if ( $is_api_delivery ) : ?>
		<div class="reply-prompt">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;"/>

			<h3 class="reply">
				<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
			</h3>
		</div>
	<?php endif; ?>

</div>