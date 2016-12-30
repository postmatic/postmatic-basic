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

<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">

	<p class="padding" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal; padding: 10px 0;">{{{subscriber_comment_intro_html}}}</p>

	<div class="new-reply" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
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
	</div>

	<?php if ( $is_api_delivery ) : ?>
		<div class="reply-prompt" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; clear: both; margin-top: 0px; margin-bottom: 20px;">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; outline: none; display: block; max-width: 100%; text-decoration: none; -ms-interpolation-mode: bicubic; width: 30px; height: 30px; margin-right: 10px; float: left;">

			<h3 class="reply" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; padding: 0; margin-top: 15px; line-height: 1.2; font-weight: 200; margin-bottom: 15px; padding-bottom: 15px; margin-left: 20px; font-size: 100%; padding-top: 5px; clear: none;">
				<a href="mailto:{{{reply_to}}}?subject=<?php
				echo rawurlencode( sprintf( __( 'In reply to %s', 'Postmatic' ), $commenter_name ) );
				?>" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; color: #404040; text-decoration: none;"><?php printf( __( 'Reply to this email to respond to %s.', 'Postmatic' ), $commenter_name ); ?>
				</a>
			</h3>
		</div>
	<?php endif; ?>
</div>


<div class="padded gray" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 25px; background: #f6f6f6;">
	<div class="context" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 90%; line-height: normal; margin-bottom: 45px;">
		<h3 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 22px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;"><?php _e( 'Here\'s a recap of this post and conversation:', 'Postmatic' ); ?></h3>

		<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
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
		<p class="excerpt" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal; font-style: italic;"><?php echo Prompt_Formatting::escape_handlebars_expressions( $subscribed_post->get_excerpt() ); ?></p>
	</div>

	<h3 class="summary" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 22px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px; clear: left;">
		<?php
		printf(
			__( 'There were <a href="%1$s">%2$d comments previous to this. Here is this reply in context:', 'Postmatic' ),
			get_permalink( $subscribed_post->id() ) . '#comments',
			wp_count_comments( $subscribed_post->id() )->approved
		);
		?>
	</h3>

	<div class="previous-comments" id="comments" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; background: #f6f6f6; clear: both; margin: 25px 0 10px 0; padding: 5px 0 5px 0;">
		<?php
		wp_list_comments( array(
			'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
			'style' => 'div',
		), $previous_comments );
		?>
	</div>

	<?php if ( $is_api_delivery ) : ?>
		<div class="reply-prompt" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; clear: both; margin-top: 0px; margin-bottom: 20px;">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; outline: none; display: block; max-width: 100%; text-decoration: none; -ms-interpolation-mode: bicubic; width: 30px; height: 30px; margin-right: 10px; float: left;">

			<h3 class="reply" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; padding: 0; margin-top: 15px; line-height: 1.2; font-weight: 200; margin-bottom: 15px; padding-bottom: 15px; margin-left: 20px; font-size: 100%; padding-top: 5px; clear: none;">
				<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
			</h3>
		</div>
	<?php endif; ?>

</div>