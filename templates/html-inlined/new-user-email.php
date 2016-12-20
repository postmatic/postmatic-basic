<?php
/*
* Template variables in scope:
* WP_User  $user       The new user
* string   $password   The new user plaintext password
*/
?>
<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">
	<h1 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 36px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;"><?php printf( __( 'Welcome to %s.', 'Postmatic' ), get_option( 'blogname' ) );?></h1>
<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
	<?php
	printf(
		__(
			'It\'s not required, but you can access some extra features on our site by <a href="%s">logging in.',
			'Postmatic'
		),
		wp_login_url()
	);
	?>
</p>
<h2 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 28px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;"><?php _e( 'Your Account Information:', 'Postmatic' ); ?></h2>
<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
	<strong style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;"><?php _e( 'Username', 'Postmatic' ); ?></strong>: <?php echo stripslashes( $user->user_login ); ?><br style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
	<strong style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;"><?php _e( 'Password', 'Postmatic' ); ?></strong>: <?php echo $password; ?>
</p>

<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
	<?php printf( __( 'You may log in by visiting %s.', 'Postmatic' ), wp_login_url() ); ?>
</p>
</div>
