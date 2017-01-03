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
<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">
	<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
<?php
		/* translators: %1$s is commenter name, %2$s is post title */
		printf(
			__( '%1$s added a comment in reply to %2$s.', 'Postmatic' ),
			'<span style="text-tranform: capitalize;" class="capitalize">' . $commenter_name . '</span>',
			'<a href="' . get_permalink( $comment_post_ID ) . '">' . get_the_title( $comment_post_ID ) . '</a>'
		);
		?>
	</p>


	<div class="primary-comment comment" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: normal; min-height: 55px; clear: left;">
		<div class="comment-header" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 100%; padding-bottom: 0; margin-bottom: 20px;">
			<?php echo $avatar; ?>
			<div class="author-name" style="margin: 0; padding: 0; font-size: 100%; font-family: serif; line-height: normal; display: inline; margin-left: 12px; font-style: italic;">
				<?php if ( $comment_author_url ) : ?>
					<a href="<?php echo esc_url( $comment_author_url ); ?>" style="margin: 0; padding: 0; font-size: 100%; color: #404040; font-family: serif; line-height: normal; font-style: italic;">
						<?php echo $commenter_name; ?>
					</a>
				<?php else : ?>
					<?php echo $commenter_name; ?>
				<?php endif; ?>
			</div>
			<div class="comment-body" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin-left: 60px; color: #000;">
				<?php echo $comment_text; ?>
			</div>
		</div>
	</div>
	
	<?php if ( ! $is_api_delivery ) : ?>
		<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
			<?php
			/* translators: %1$s is commenter name, %2$s is comment URL */
			printf(
				__( 'To reply to %1$s <a href="%2$s">visit this conversation on the web.</a>', 'Postmatic' ),
				$commenter_name,
				get_comment_link( $comment_ID )
			);
			?>
		</p>
	<?php endif; ?>

	<?php if ( count( $previous_comments ) > 1 and $is_api_delivery ) : ?>

		<div class="reply-prompt" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; clear: both; margin-top: 0px; margin-bottom: 20px;">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; outline: none; display: block; max-width: 100%; text-decoration: none; -ms-interpolation-mode: bicubic; width: 30px; height: 30px; margin-right: 10px; float: left;">

			<h3 class="reply" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; padding: 0; margin-top: 15px; line-height: 1.2; font-weight: 200; margin-bottom: 15px; padding-bottom: 15px; margin-left: 20px; font-size: 100%; padding-top: 5px; clear: none;">
				<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
				<small style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; margin-left: 20px; font-size: 90%; display: block;">
					<br style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
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


	<div class="padded gray postmatic-content" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 25px; background: #f6f6f6;">
		<h3 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 22px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;"><?php _e( 'Recently in this conversation...', 'Postmatic' ); ?></h3>

		<div class="previous-comments" id="comments" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; background: #f6f6f6; clear: both; margin: 10px 20px; padding: 5px 25px;">
			<?php
			wp_list_comments( array(
				'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
				'style' => 'div',
			), $previous_comments );
			?>
		</div>

	<?php endif; ?>

	<?php if ( $is_api_delivery ) : ?>
	<div class="reply-prompt" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; clear: both; margin-top: 0px; margin-bottom: 20px;">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; outline: none; display: block; max-width: 100%; text-decoration: none; -ms-interpolation-mode: bicubic; width: 30px; height: 30px; margin-right: 10px; float: left;">

		<h3 class="reply" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; padding: 0; margin-top: 15px; line-height: 1.2; font-weight: 200; margin-bottom: 15px; padding-bottom: 15px; margin-left: 20px; font-size: 100%; padding-top: 5px; clear: none;">
			<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
			<small style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; margin-left: 20px; font-size: 90%; display: block;">
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



