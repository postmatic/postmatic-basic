<?php
/**
 * Template for forwarded messages.
 *
 * @var WP_User $sender
 * @var string  $message
 */
?>
<div class="padded">
	<h2>
		<?php
		printf(
			__( '%s sent you a private message', 'Postmatic' ),
			$sender->display_name
		);
		?>
	</h2>

	<div>
		<?php echo $message; ?>
	</div>
</div>