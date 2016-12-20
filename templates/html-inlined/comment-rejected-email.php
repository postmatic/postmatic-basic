<?php
/**
* comment rejected email template
* variables in scope:
* @var WP_User $comment_author
* @var WP_Post $post False if the post no longer exists.
* @var string $post_title Post title or placeholder if post no longer exists
*/
?>
<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">
	<h1 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 36px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;"><?php _e( "We're sorry.", 'Postmatic' ); ?></h1>
<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
	<?php
	printf(
		__(
			'Your reply to <em>%s cannot be published because the post cannot be found or the discussion has been closed.',
			'Postmatic'
		),
		$post_title
	);
	?> 
</p>

<?php if ( $post ) : ?>
	<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
		<?php
		printf(
			__( 'Please visit %s for more information.', 'Postmatic' ),
			'<a href="' . get_permalink( $post ) . '">' . get_permalink( $post ) . ''
		);
		?>
	</p>
	<a href="<?php echo get_permalink( $post ); ?>#comments" class="btn-primary" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; padding: 0; line-height: 2; color: #FFF; text-decoration: none; background-color: #348eda; border-radius: 25px; border-width: 10px 20px; font-weight: bold; margin-right: 10px; margin-bottom: 10px; text-align: center; cursor: pointer; display: inline-block; border: solid #348eda;">
		<?php _e( 'More Information', 'Postmatic' ); ?>
	</a>
<?php endif; ?>
</div>