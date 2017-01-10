<?php
/**
 * @var Prompt_Interface_Subscribable[] $lists
 * @var string                          $invite_introduction Only present for invite emails.
 * @var bool                            $is_api_transport
 */
?>
<?php if ( !empty( $invite_introduction ) ) : ?>
	<h1><?php printf( __( 'An invitation from %s', 'Postmatic' ), get_bloginfo( 'name' ) ); ?></h1>

	<?php echo $invite_introduction; ?>
<?php else : ?>
	<h1>
		<?php /* translators: %s is site name */ ?>
		<?php printf( __( '%s Subscription Confirmation', 'Postmatic' ), get_bloginfo( 'name' ) ); ?>
	</h1>
<?php endif; ?>

{{notice_text}}

<?php if ( count( $lists ) < 2 ) : ?>
	<p>
		<?php
		printf(
			__(
				'To confirm your subscription, please visit this web page:',
				'Postmatic'
			)
		);
		?>
		<br/>
		{{{opt_in_url}}}
	</p>
<?php else : ?>
	<p>
		<?php
		printf(
			__(
				'Thanks for subscribing to %s. There is one more step to verify your subscription.',
				'Postmatic'
			),
			get_bloginfo( 'name' )
		);
		?>
	</p>
	<p><?php _e( 'Please choose a subscription option', 'Postmatic' ); ?></p>
	<ol>
		<?php foreach ( $lists as $list ) : ?>
			<li>
				<?php echo $list->select_reply_prompt( Prompt_Enum_Content_Types::TEXT ); ?>
			</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>

<p>
	<?php
	printf(
		__(
			'If you did not initiate this subscription please ignore this email or forward it to %s.',
			'Postmatic'
		),
		Prompt_Core::ABUSE_EMAIL
	);
	?>
</p>