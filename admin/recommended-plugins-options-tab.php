<?php

/**
 * Options tab for recommending plugins
 *
 * @since 2.1.0
 *
 */
class Prompt_Admin_Recommended_Plugins_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Recommended Plugins', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function render() {
		$asides = array();

		if ( ! defined( 'EPOCH_VER' ) ) {
			$asides[] = html(
				'aside',
				html( 'h3', __( 'Make comments fun & fast with Epoch', 'Postmatic' ) ),
				html(
					'p',
					__(
						'<a href="http://gopostmatic.com/epoch" target="_blank">Epoch</a> is a free, private, and native alternative to Disqus. Your users will love it and your site speed score will as well.',
						'Postmatic'
					)
				),
				html(
					'a class="button"',
					array( 'href' => wp_nonce_url(
						admin_url( 'update.php?action=install-plugin&plugin=epoch' ),
						'install-plugin_epoch'
					) ),
					__( 'Install Epoch', 'Postmatic' )
				)
			);
		}

		if ( ! class_exists( 'Postmatic_Social' ) ) {
			$asides[] = html(
				'aside',
				html( 'h3', __( 'Get rid of the comment form with Postmatic Social Commenting', 'Postmatic' ) ),
				html(
					'p',
					__(
						'Install Postmatic Social Commenting, a tiny, fast, and convenient way to let your readers comment using their social profiles.',
						'Postmatic'
					)
				),
				html(
					'a class="button"',
					array( 'href' => wp_nonce_url(
						admin_url( 'update.php?action=install-plugin&plugin=postmatic-social-commenting' ),
						'install-plugin_postmatic-social-commenting'
					) ),
					__( 'Install Social Commenting', 'Postmatic' )
				)
			);
		}

		if ( ! class_exists( 'Sift_Ninja' ) ) {
			$asides[] = html(
				'aside',
				html( 'h3', __( 'Filter profanity, bullying, harassment, and trolls with Sift Ninja', 'Postmatic' ) ),
				html(
					'p',
					__(
						'The quickest, cleverest and most accurate filter for auto-moderating comments. Language isn’t black and white so your filter shouldn’t be either.',
						'Postmatic'
					),
					' ',
					html(
						'strong',
						html(
							'a',
							array( 'href' => admin_url( 'options-general.php?page=postmatic-pricing' ) ),
							__( 'Upgrade Replyable and get 25% off 6 months of premium Sift Ninja service!', 'Postmatic' )

						)
					)
				),
				html(
					'a class="button"',
					array( 'href' => wp_nonce_url(
						admin_url( 'update.php?action=install-plugin&plugin=sift-ninja' ),
						'install-plugin_sift-ninja'
					) ),
					__( 'Install Sift Ninja', 'Postmatic' )
				)
			);
		}

		if ( ! class_exists( 'elevated_comments' ) ) {
			$asides[] = html(
				'aside',
				html( 'h3', __( 'Comments can be the best part of a post. So why are they always buried?', 'Postmatic' ) ),
				html(
					'p',
					__(
						'<a href="http://elevated.gopostmatic.com" target="_blank">Elevated Comments</a> uses language analysis and machine learning to identify the most relevant and thoughtful comment on each of your posts. The comment is then automatically inserted near the top of the post as a simple sidebar pull quote.',
						'Postmatic'
					)
				),
				html(
					'a class="button"',
					array( 'href' => wp_nonce_url(
						admin_url( 'update.php?action=install-plugin&plugin=elevated-comments' ),
						'install-plugin_elevated-comments'
					) ),
					__( 'Install Elevated Comments', 'Postmatic' )
				)
			);
		}

		return html(
			'fieldset class="chooser"',
			html(
				'div class="intro-text"',
				html( 'h2', __( 'Looking to solve commenting? You\'re going to want these free plugins:', 'Postmatic' ) ),
				implode( '', $asides )
			)
		);
	}
}

