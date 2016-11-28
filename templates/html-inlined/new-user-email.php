<?php
/*
* Template variables in scope:
* WP_User  $user       The new user
* string   $password   The new user plaintext password
*/
?>
<div class="padded">
	<h1><?php printf( __( 'Welcome to %s.', 'Postmatic' ), get_option( 'blogname' ) );?></h1>
<p>
	<?php
	printf(
		__(
			'It\'s not required, but you can access some extra features on our site by <a href="%s">logging in</a>.',
			'Postmatic'
		),
		wp_login_url()
	);
	?>
</p>
<h2><?php _e( 'Your Account Information:', 'Postmatic' ); ?></h2>
<p>
	<strong><?php _e( 'Username', 'Postmatic' ); ?></strong>: <?php echo stripslashes( $user->user_login ); ?><br />
	<strong><?php _e( 'Password', 'Postmatic' ); ?></strong>: <?php echo $password; ?>
</p>

<p>
	<?php printf( __( 'You may log in by visiting %s.', 'Postmatic' ), wp_login_url() ); ?>
</p>
</div>