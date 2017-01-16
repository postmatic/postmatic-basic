<?php
/**
 * Template variables in scope:
 * @var Prompt_Interface_Subscribable $object The thing being subscribed to
 * @var array $comments Comments since flood control
 */
?>
<h1>{{welcome_back_message}}</h1>

<?php echo $object->subscription_description(); ?>


<?php if ( $comments ) : ?>
	------
	<p>
		<?php _e( 'Here\'s a recap of the conversation. You\'ll see a marker below showing you what\'s new. Reply to add your thoughts.', 'Postmatic' ); ?>
	</p>

	<div>
		<?php
		wp_list_comments( array(
			'callback' => array( 'Prompt_Email_Comment_Rendering', 'render_text' ),
			'end-callback' => '__return_empty_string',
			'style' => 'div',
		), $comments );
		?>
	</div>

	<p>
		<?php _e( 'View this conversation online', 'Postmatic' ); ?><br/>
		<?php echo get_the_permalink( $object->id() ); ?>#comments
	</p>

	<p>
		<?php _e( 'To leave a comment simply reply to this email.', 'Postmatic' ); ?>
	</p>

	<p>
		<?php
		printf(
			__(
				'Please note: Your reply will be published publicly and immediately on %s.',
				'Postmatic'
			),
			get_bloginfo( 'name' )
		);
		?>
	</p>
<?php endif; ?>

<p>
	<?php printf( __( 'To unsubscribe at any time visit %s', 'Postmatic' ), $object->subscription_url() ); ?>
</p>
