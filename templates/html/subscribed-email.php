<?php
/**
* Template variables in scope:
* @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
* @var string $subscribed_introduction Custom introduction content.
* @var array                 $comments      Comments so far for post subscriptions
*/
?>
<div class="padded">
	<h3>{{{welcome_message}}}</h3>
	<p><?php echo $object->subscription_description(); ?></p>

	<div id="subscribed-introduction">
		<?php
		if ( $subscribed_introduction ) :
			printf( $subscribed_introduction );
		elseif ( $comments ) :
			printf( '<h3>%s</h3>', __( 'Here is what others have to say. Reply to add your thoughts.', 'Postmatic' ) );
		endif;
		?>
	</div>

	<?php if ( $comments ) : ?>

		<h3><?php __( "Want to catch up? Here are the 30 most recent comments:", 'Postmatic' ); ?></h3>

		<div class="previous-comments padded">
			<?php
			wp_list_comments( array(
				'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
				'style' => 'div',
			), $comments );
			?>
		</div>

		<p id="button"><a href="<?php echo get_the_permalink( $object->id() ); ?>#comments" class="btn-secondary">
				<?php _e( 'View this conversation online', 'Postmatic' ); ?></a>
		</p>

		<div class="reply-prompt">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30"
			     align="left" style="float: left; margin-right: 10px;"/>

			<p class="reply">
				<?php _e( 'Reply to this email to add a comment. Your email address will not be shown.', 'Postmatic' ); ?>
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
				</small>
			</p>
		</div>
	<?php endif; ?>

</div>