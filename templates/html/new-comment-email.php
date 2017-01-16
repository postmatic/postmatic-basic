<?php
/**
 * comment notification email template
 * variables in scope:
 * @var int                 $comment_ID
 * @var {WP_User}           $comment_author
 * @var string              $commenter_name
 * @var string              $comment_post_ID
 * @var string              $comment_author_url
 * @var string              $comment_text
 * @var string              $avatar
 * @var Prompt_Post         $subscribed_post
 * @var string              $subscribed_post_title_link
 * @var array               $previous_comments
 * @var bool                $is_api_delivery
 * @var string              $post_author_message
 */

$previous_index = count( $previous_comments );
?>
<div class="padded">
	<p>
		<?php
		/* translators: %1$s is commenter name, %2$s is post title */
		printf(
			__( '%1$s added a comment in reply to %2$s.', 'Postmatic' ),
			'<span style="text-tranform: capitalize;" class="capitalize">' . $commenter_name . '</span>',
			'<a href="' . get_permalink( $comment_post_ID ) . '">' . get_the_title( $comment_post_ID ) . '</a>'
		);
		?>
	</p>
	
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
	
	<?php if ( ! $is_api_delivery ) : ?>
		<p>
			<?php
			/* translators: %1$s is commenter name, %2$s is comment URL */
			printf(
				__( 'To reply to %1$s <a href="%2$s">visit this conversation on the web</a>.', 'Postmatic' ),
				$commenter_name,
				get_comment_link( $comment_ID )
			);
			?>
		</p>
	<?php endif; ?>

	<?php if ( count( $previous_comments ) > 1 and $is_api_delivery ) : ?>

		<div class="reply-prompt">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;"/>

			<h3 class="reply">
				<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
				<small>
					<br/>
					<?php
					printf(
						__(
							'You\'re invited to respond by replying to this email. If you do, it may be published immediately or held for moderation, depending on the comment policy of %s.',
							'Postmatic'
						),
						$subscribed_post_title_link
					);
					?>
				</small>
			</h3>
		</div>

	</div>


	<div class="padded gray postmatic-content">
		<h3><?php _e( 'Recently in this conversation...', 'Postmatic' ); ?></h3>

		<div class="previous-comments" id="comments">
			<?php
			wp_list_comments( array(
				'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
				'style' => 'div',
			), $previous_comments );
			?>
		</div>

	<?php endif; ?>

	<?php if ( $is_api_delivery ) : ?>
	<div class="reply-prompt">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;"/>

		<h3 class="reply">
			<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
			<small>
				<?php
				printf(
					__(
						'<br /><strong>Please note</strong>: Your reply will be published on %s.',
						'Postmatic'
					),
					'<a href="' . get_permalink( $comment_post_ID ) . '">' .
					get_the_title( $comment_post_ID ) . '</a>'
				);
				?>
			</small>
		</h3>
	</div>
	<?php endif; ?>

	{{{post_author_message}}}
</div>

