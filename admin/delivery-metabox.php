<?php

class Prompt_Admin_Delivery_Metabox extends scbPostMetabox {

	/** @var string */
	static protected $no_email_name = 'prompt_no_email';
	/** @var string */
	static protected $no_featured_image_name = 'prompt_no_featured_image';
	/** @var string */
	static protected $preview_email_name = 'prompt_preview_email';
	/** @var string  */
	static protected $excerpt_only_name = 'prompt_excerpt_only';
	/** @var string  */
	static protected $retry_failed_recipients_name = 'prompt_retry_failed_recipients';

	/** @var WP_Post */
	protected $post;
	/** @var  array */
	protected $recipient_ids;
	/** @var  array */
	protected $sent_recipient_ids;

	/**
	 * Find out if the "no email" metabox checkbox was checked for a post.
	 * @param int $post_id
	 * @return bool
	 */
	public static function suppress_email( $post_id ) {

		if ( isset( $_POST[self::$no_email_name] ) and isset( $_POST['post_ID'] ) and $_POST['post_ID'] == $post_id )
			return true; // Meta hasn't been saved yet but will be

		$meta_values = get_post_meta( $post_id, self::$no_email_name );

		if ( !empty( $meta_values ) )
			return (bool) $meta_values[0];

		return Prompt_Core::$options->get( 'no_post_email_default' );
	}

	/**
	 * Find out if the "no featured image" metabox checkbox was checked.
	 * @param int $post_id
	 * @return bool
	 */
	public static function suppress_featured_image( $post_id ) {

		if ( isset( $_GET['action'] ) and 'prompt_post_delivery_preview' == $_GET['action'] )
			return intval( $_GET['post_id'] ) == $post_id and !empty( $_GET[self::$no_featured_image_name] );

		if (
			isset( $_POST['post_ID'] ) and
			intval( $_POST['post_ID'] ) == $post_id and
			isset( $_POST[self::$no_featured_image_name] )
		) {
			return true; // Meta hasn't been saved yet but will be
		}

		$meta_values = get_post_meta( $post_id, self::$no_featured_image_name );

		if ( !empty( $meta_values ) )
			return (bool) $meta_values[0];

		return Prompt_Core::$options->get( 'no_post_featured_image_default' );
	}

	/**
	 * Find out if the "excerpt only" metabox checkbox was checked.
	 * @param int $post_id
	 * @return bool
	 */
	public static function excerpt_only( $post_id ) {

		if ( isset( $_GET['action'] ) and 'prompt_post_delivery_preview' == $_GET['action'] )
			return intval( $_GET['post_id'] ) == $post_id and !empty( $_GET[self::$excerpt_only_name] );

		if (
			isset( $_POST['post_ID'] ) and
			intval( $_POST['post_ID'] ) == $post_id and
			isset( $_POST[self::$excerpt_only_name] )
		) {
			return true; // Meta will be saved by our parent class
		}

		$meta_values = get_post_meta( $post_id, self::$excerpt_only_name );

		if ( !empty( $meta_values ) )
			return (bool) $meta_values[0];

		return Prompt_Core::$options->get( 'excerpt_default' );
	}

	/**
	 * Get current delivery status information for a post.
	 * @param int $post_id
	 * @return array {
	 *   @type string $description
	 *   @type int $recipient_count
	 *   @type int $sent_count
	 *   @type int $failed_count
	 * }
	 */
	public static function status( $post_id ) {

		$prompt_post = new Prompt_Post( $post_id );

		$recipient_count = count( $prompt_post->recipient_ids() );
		$failed_count = count( $prompt_post->failed_recipient_ids() );
		$sent_count = count( array_diff( $prompt_post->sent_recipient_ids(), $prompt_post->failed_recipient_ids() ) );

		if ( $failed_count > 0 ) {

			$description = self::failed_count_description( $sent_count, $failed_count );

		} elseif ( $sent_count == 0 and 'publish' != $prompt_post->get_wp_post()->post_status ) {

			$description = self::recipient_count_description( $recipient_count );

		} else {

			$description = self::sent_count_description( $sent_count );

		}

		return compact( 'description', 'sent_count', 'recipient_count' );
	}

	/**
	 * Enqueue post editor javascript.
	 *
	 * @since 2.0.14
	 */
	public function admin_enqueue_scripts() {
		$script = new Prompt_Script( array(
			'handle' => 'prompt-post-editor',
			'path' => 'js/post-editor.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();
	}

	/**
	 * Get empty status HTML with a spinner.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_status() {

		return html( 'p',
			array( 'class' => 'status' ),
			html( 'span', array( 'class' => 'spinner' ) )
		);

	}

	/**
	 * Emit metabox HTML.
	 *
	 * @since 2.0.0
	 * @param object $post
	 */
	public function display( $post ) {
		$this->set_post( $post );
		echo $this->render_form();
		echo $this->render_status();
	}

	/**
	 * Get form HTML for the metabox.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_form() {
		$form_html = '';

		if ( 'publish' == $this->post->post_status or count( $this->sent_recipient_ids ) >= count( $this->recipient_ids ) )
			return $form_html;

		$form_html .= html( 'p',
			scbForms::input(
				array(
					'type' => 'checkbox',
					'name' => self::$no_email_name,
					'desc' => __( 'Do not deliver this post via email.', 'Postmatic' ),
					'checked' => self::suppress_email( $this->post->ID ),
				)
			)
		);

		$form_html .= html( 'p',
			scbForms::input(
				array(
					'type' => 'checkbox',
					'name' => self::$excerpt_only_name,
					'desc' => __( 'Include only the post excerpt in email.', 'Postmatic' ),
					'checked' => self::excerpt_only( $this->post->ID ),
				)
			)
		);

		if ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) ) {

			$form_html .= html( 'p',
				scbForms::input(
					array(
						'type' => 'checkbox',
						'name' => self::$no_featured_image_name,
						'desc' => __( 'Do not use the featured image in email.', 'Postmatic' ),
						'checked' => self::suppress_featured_image( $this->post->ID ),
					)
				)
			);

		}

		$form_html .= html( 'p',
			html( 'input',
				array(
					'type' => 'submit',
					'name' => self::$preview_email_name,
					'value' => __( 'Send me a preview email', 'Postmatic' ),
					'class' => 'button',
				)
			)
		);

		return $form_html;
	}

	/**
	 * @since 2.0.0
	 * @param array $post_data
	 * @param int $post_id
	 * @return array
	 */
	protected function before_save( $post_data, $post_id ) {
		$post_data =  array(
			self::$no_email_name => isset( $_POST[self::$no_email_name] ),
			self::$no_featured_image_name => isset( $_POST[self::$no_featured_image_name] ),
			self::$excerpt_only_name => isset( $_POST[self::$excerpt_only_name] ),
		);

		// Make changes to featured image suppression sticky
		if ( $post_data[self::$no_featured_image_name] != Prompt_Core::$options->get( 'no_post_featured_image_default' ) ) {
			Prompt_Core::$options->set( 'no_post_featured_image_default', $post_data[self::$no_featured_image_name] );
		}

		if ( isset( $_POST[self::$retry_failed_recipients_name] ) ) {
			$prompt_post = new Prompt_Post( $post_id );
			$prompt_post->remove_sent_recipient_ids( $prompt_post->failed_recipient_ids() );
			$prompt_post->remove_failed_recipient_ids( $prompt_post->failed_recipient_ids() );
			Prompt_Post_Mailing::send_notifications( $post_id );
		}

		return $post_data;
	}

	/**
	 * @since 2.0.0
	 * @param WP_Post $post
	 */
	protected function set_post( $post ) {
		$this->post = $post;
		$prompt_post = new Prompt_Post( $post );
		$this->recipient_ids = $prompt_post->recipient_ids();
		$this->sent_recipient_ids = $prompt_post->sent_recipient_ids();
	}

	/**
	 * @param int $recipient_count
	 * @return string
	 */
	protected static function recipient_count_description( $recipient_count ) {

		if ( $recipient_count == 0 )
			return __( 'No emails will be sent for this post.', 'Postmatic' );

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) ) {

			$description = sprintf(
				__( 'Your post will be sent to as plain text to %d subscribers and deliverability will depend on the outgoing mail of your web host. <br />.', 'Postmatic' ),
				$recipient_count
			);

			$description .= ' ' . html( 'a',
				array( 'href' => self::upgrade_url(), 'class' => 'upgrade_postmatic' ),
				__( 'Have a list larger than a few hundred? Let us deliver your mail for you.', 'Postmatic' )
			);

			return $description;

		}

		return sprintf(
			__( 'This post will be sent to every one of your %d readers using our top-notch mail servers. Thanks for supporting Postmatic.', 'Postmatic' ),
			$recipient_count
		);

	}

	/**
	 * @param int $sent_count
	 * @return string
	 */
	protected static function sent_count_description( $sent_count ) {

		if ( $sent_count == 0 )
			return __( 'No emails have been sent for this post.', 'Postmatic' );

		$description = sprintf(
			__( 'This post was sent to %d subscribers.', 'Postmatic' ),
			$sent_count
		);

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) ) {

			$description .= ' ' . html( 'a',
				array( 'href' => self::upgrade_url(), 'class' => 'button button-primary' ),
				__( 'Upgrade to Postmatic Premium', 'Postmatic' )
			);

		}

		return $description;

	}

	/**
	 * @param int $sent_count
	 * @param int $failed_count
	 * @return string
	 */
	protected static function failed_count_description( $sent_count, $failed_count ) {

		$description = html( 'p class="wp-ui-text-notification"',
			sprintf(
				__( 'This post was sent successfully to %d subscribers but WordPress mailing failed for %s subscribers.', 'Postmatic' ),
				$sent_count,
				$failed_count
			)
		);

		$description .= html( 'p',
			sprintf(
				__(
					'There could be many reasons for this, but many web hosts enforce limits on how much email you can send at once. If this continues to be a problem consider upgrading to a <a href="%s" target="_blank">paid Postmatic account</a> and let our servers send your email with guaranteed delivery.',
					'Postmatic'
				),
				'https://gopostmatic.com/vs'
			)
		);

		$description .= html( 'p',
			html(
				'input',
				array(
					'name' => self::$retry_failed_recipients_name,
					'type' => 'submit',
					'class' => 'button',
					'value' => __( 'Retry failed WordPress emails', 'Postmatic' )
				)
			)
		);

		$description .= ' ' . html( 'a',
			array( 'href' => self::upgrade_url(), 'class' => 'button button-primary' ),
			__( 'Upgrade to Postmatic Premium', 'Postmatic' )
		);

		return $description;

	}

	protected static function upgrade_url() {
		return Prompt_Enum_Urls::MANAGE . '/login';
	}
}