<?php
/**
 * @var Prompt_Interface_Subscribable[] $lists
 * @var string                          $invite_introduction Only present for invite emails.
 * @var bool                            $is_api_transport
 */
?>

<div class="padded" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0 20px 20px 20px;">
	<?php if ( !empty( $invite_introduction ) ) : ?>
		<?php /* translators: %s is site name */ ?>
		<h3 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 22px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; margin-top: 15px;"><?php printf( __( 'An invitation from %s', 'Postmatic' ), get_bloginfo( 'name' ) ); ?></h3>
		<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;"><?php echo $invite_introduction; ?></p>
	<?php else : ?>
		<h4 style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.2; margin-bottom: 5px; font-weight: normal; margin-top: 15px;">
			<strong style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
				<?php
				if ( count( $lists ) < 2 ) {
					printf(
						__( "Action required: There’s one more step to confirm your subscription to %s.", 'Postmatic' ),
						$lists[0]->subscription_object_label()
					);
				} else {
					_e( "Action required: There's one more step to confirm your subscription.", 'Postmatic' );
				}
				?>
			</strong>
		</h4>
	<?php endif; ?>

	{{{notice_html}}}

	<?php if ( count( $lists ) < 2 ) : ?>
		<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
			<span class="alert" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 2px; background: #FFFEBA; font-weight: normal;">
				<?php
				printf(
					__(
						'To confirm your subscription, <a href="%s" style="%s"><strong style="%s">click here</strong></a>.',
						'Postmatic'
					),
					'{{{opt_in_url}}}',
					"margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; color: #404040;",
					"margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;"
				);
				?>
			</span>
		</p>
	<?php else : ?>
		<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
			<span class="alert" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 2px; background: #FFFEBA; font-weight: normal;">
				<?php
				printf(
					__(
						'Please choose a subscription option:',
						'Postmatic'
					),
					get_bloginfo( 'name' )
				);
				?>
			</span>
		</p>
		<ol style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
			<?php foreach ( $lists as $list ) : ?>
				<li style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin-left: 25px; list-style-position: inside; list-style-type: decimal;">
					<?php echo $list->select_reply_prompt(); ?>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>

<div class="padded gray" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 25px; background: #f6f6f6;">

	<p class="abuse" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; margin-bottom: 10px; font-weight: normal; font-size: 85%;">
		<?php
		printf(
			__(
				'If you did not initiate this subscription please ignore this email or forward it to %s.',
				'Postmatic'
			),
			Prompt_Core::ABUSE_EMAIL
		)
		?>
	</p>

</div>