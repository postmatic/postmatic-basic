<?php
/**
 * Template variables in scope:
 * @var WP_User $subscriber
 * @var Prompt_Interface_Subscribable $object The thing being subscribed to
 * @var WP_Post $subscribed_introduction Custom introduction content.
 * @var array $comments Comments so far for post subscriptions
 */
?>
<h1>{{welcome_message}}</h1>

<p>
	<?php echo $object->subscription_description(); ?>
</p>

<h2><?php _e( "What's next?", 'Postmatic' ); ?></h2>

<p>
	<?php
	printf(
		__( 'Keep an eye on your inbox for %s.', 'Postmatic' ),
		$object->subscription_object_label()
	);
	?>
</p>

<p>
	<?php
	if ( $subscribed_introduction ) :
		printf( $subscribed_introduction );
	elseif ( $comments ) :
		_e( 'The conversation so far is included below.', 'Postmatic' );
	endif;
	?>
</p>

<?php if ( $comments ) : ?>

	<h2><?php _e( "Here is the discussion so far", 'Postmatic' ); ?></h2>

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
		<?php _e( '* To leave a comment simply reply to this email. *', 'Postmatic' ); ?><br/>

		<?php
		printf(
			__(
				'Please note: Your reply will be published on %s.',
				'Postmatic'
			),
			get_bloginfo( 'name' )
		);
		?>
	</p>
<?php endif; ?>

