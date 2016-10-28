<?php
/**
 * Template variables in scope:
 * @var string   $status    The result of the requested transaction
 */
?>
<div class="padded">
	<p><?php echo $status; ?></p>
	<p>
		<a href="<?php echo home_url(); ?>"><?php bloginfo( 'name' ); ?></a>
	</p>
</div>