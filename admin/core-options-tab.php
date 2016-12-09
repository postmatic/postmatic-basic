<?php

/**
 * Options tab for choosing modules
 *
 * @since 2.0.0
 *
 */
class Prompt_Admin_Core_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 *
	 * @since 2.0.0
	 *
	 * @param Prompt_Options $options
	 * @param array|null $overridden_options
	 */
	public function __construct( $options, $overridden_options = null ) {
		parent::__construct( $options, $overridden_options );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Get Started', 'Postmatic' );
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'core';
	}

	/**
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function render() {

		$parts = array(
			$this->promo_html(),
			html(
				'div class="intro-text welcome"',
				html( 'h2', __( 'Welcome to Replyable', 'Postmatic' ) ),
				html(
					'p',
					__( 'The best engagement and commenting solution for WordPress.', 'Postmatic' )
				),
				$this->video_link( 'yjbVIBiSyYE' )
			),
		);

		$parts[] = $this->feature_chooser_html();

		$parts[] = html( 'div id="manage-account"',
			html( 'a',
				array( 'href' => '/wp-admin/options-general.php?page=postmatic-account', 'target' => '_blank' ),
				__( '&#9998; Manage your account', 'Postmatic' )
			)
		);

		return $this->form_wrap( implode( '', $parts ) );
	}

	/**
	 * Disable overridden entry UI table entries.
	 *
	 * @since 2.0.0
	 *
	 * @param array $table_entries
	 */
	protected function override_entries( &$table_entries ) {
		foreach ( $table_entries as $index => $entry ) {
			if ( isset( $this->overridden_options[$entry['name']] ) ) {
				$table_entries[$index]['extra'] = array(
					'class' => 'overridden',
					'disabled' => 'disabled',
				);
			}
		}
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function promo_html() {
		if ( $this->options->is_api_transport() ) {
			return '';
		}
		$template = new Prompt_Template( 'core-options-promo.php' );
		return $template->render();
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function feature_chooser_html() {

		$choosers = array(
			$this->comment_chooser_html(),
		);

		return implode( '', $choosers );
	}

	/**
	 * @since 2.0.6
	 * @param string $video_id
	 * @return string
	 */
	protected function video_link( $video_id ) {
		return html(
			'a',
			array(
				'class' => 'thickbox video',
				'href' => "https://www.youtube.com/embed/$video_id?autoplay=1&TB_iframe=true",
			),
			html(
				'span',
				__( 'Watch the Video', 'Postmatic' )
			)
		);
	}

	/**
	 * @since 2.0.0
	 * @return string
	 */
	protected function comment_chooser_html() {
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
						'The quickest, cleverest and most accurate filter for auto-moderating comments. Language isn’t black and white so your filter shouldn’t be either. <strong><a href="/wp-admin/options-general.php?page=postmatic-pricing">Upgrade Replyable and get 25% off 6 months of premium Sift Ninja service</a>!</strong>',
						'Postmatic'
					)
				),
				html(
					'a class="button"',
					array( 'href' => wp_nonce_url(
						admin_url( 'update.php?action=install-plugin&plugin=postmatic-social-commenting' ),
						'install-plugin_postmatic-social-commenting'
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
						admin_url( 'update.php?action=install-plugin&plugin=postmatic-social-commenting' ),
						'install-plugin_postmatic-social-commenting'
					) ),
					__( 'Install Elevated Comments', 'Postmatic' )
				)
			);
		}


		return html(
			'fieldset class="chooser"',
			html( 'legend', __( 'Looking to solve commenting? You\'re going to want these free plugins:', 'Postmatic' ) ),
			implode( '', $asides )
		);
	}

}