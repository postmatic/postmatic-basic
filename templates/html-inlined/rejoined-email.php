<?php
/**
 * Template variables in scope:
 * @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
 * @var array                 $comments      Comments since flood control
 */
?>
<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">
	<h3 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 22px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;">{{welcome_back_message}}</h3>


	<?php if ( $comments ) : ?>
		<h4 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.2; margin-bottom: 5px; font-weight: normal; margin-top: 15px;"><?php _e( 'Here\'s a recap of the conversation.', 'Postmatic' ); ?></h4>

		<div class="previous-comments rejoin" id="comments" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; background: #f6f6f6; clear: both; margin: 10px 20px; padding: 5px 25px;">
			<?php
			wp_list_comments( array(
				'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
				'style' => 'div',
			), $comments );
			?>
		</div>

		<p id="button" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal; clear: both; margin-top: 25px;"><a href="<?php echo get_the_permalink( $object->id() ); ?>#comments" class="btn-secondary" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; padding: 0; line-height: 2; color: #FFF; text-decoration: none; background-color: #aaa; margin-top: 10px; border-width: 5px 10px; font-weight: normal; margin-right: 10px; text-align: center; cursor: pointer; display: inline-block; border-radius: 15px; border: solid #aaa;">
				<?php _e( 'View this conversation online', 'Postmatic' ); ?></a>
		</p>

		<div class="reply-prompt" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; clear: both; margin-top: 0px; margin-bottom: 20px;">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" align="left" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; outline: none; display: block; max-width: 100%; text-decoration: none; -ms-interpolation-mode: bicubic; width: 30px; height: 30px; margin-right: 10px; float: left;">
			<p class="reply" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; font-weight: normal; padding-bottom: 15px; margin-left: 20px; clear: none; margin-bottom: 0;">
				<?php
				_e( 'Reply to this email to add a comment. Your email address will not be shown.', 'Postmatic' );
				?><br style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
				<small style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block; margin-left: 20px;">
					<?php
					printf(
						__(
							'<strong>Please note: Your reply will be published publicly and immediately on %s.',
							'Postmatic'
						),
						get_bloginfo( 'name' )
					);
					?>
				</small>
			</p>
		</div>
	<?php endif; ?>

	<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
		<?php
		printf(
			__(
				'To unsubscribe at any time reply with the word <strong>unsubscribe.',
				'Postmatic'
			),
			$object->subscription_url()
		);
		?>
	</p>

</div>