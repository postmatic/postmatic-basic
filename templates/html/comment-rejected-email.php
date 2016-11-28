<?php
/**
* comment rejected email template
* variables in scope:
* @var WP_User $comment_author
* @var WP_Post $post False if the post no longer exists.
* @var string $post_title Post title or placeholder if post no longer exists
*/
?>
<div class="padded">
	<h1><?php _e( "We're sorry.", 'Postmatic' ); ?></h1>
<p>
	<?php
	printf(
		__(
			'Your reply to <em>%s</em> cannot be published because the post cannot be found or the discussion has been closed.',
			'Postmatic'
		),
		$post_title
	);
	?> 
</p>

<?php if ( $post ) : ?>
	<p>
		<?php
		printf(
			__( 'Please visit %s for more information.', 'Postmatic' ),
			'<a href="' . get_permalink( $post ) . '">' . get_permalink( $post ) . '</a>'
		);
		?>
	</p>
	<a href="<?php echo get_permalink( $post ); ?>#comments" class="btn-primary">
		<?php _e( 'More Information', 'Postmatic' ); ?>
	</a>
<?php endif; ?>
</div>