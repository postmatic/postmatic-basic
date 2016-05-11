<?php

/**
 * Optins options tab
 *
 * @since 1.4.0
 */
class Prompt_Admin_Optins_Options_Tab extends Prompt_Admin_Options_Tab {

	/**
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Configure Optins', 'Postmatic' );
	}

	/**
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'optins';
	}

	/**
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public function render() {

		$values = Prompt_Optins::optins_options();

		$default_image_url = Prompt_Core::$url_path . '/media/optins/e-black-50.png';

		$popup_image = new Prompt_Attachment_Image( $values['optins_popup_image'], $default_image_url, 500, 500 );

		$inpost_image = new Prompt_Attachment_Image( $values['optins_inpost_image'], $default_image_url, 500, 500 );

		$bottom_image = new Prompt_Attachment_Image( $values['optins_bottom_image'], $default_image_url, 500, 500 );

		$rows = array(
			$this->input(
				array( 'name' => 'optins_default_image_url', 'type' => 'hidden', 'value' => $default_image_url )
			)
		);
		
		$rows[] = html(
			'div class="intro-text"',
					html( 'h2', __( 'Add optin forms to your site and turn visitors into subscribers', 'Postmatic' ) ),
					html( 'P',
						__(
							'Postmatic comes bundled with four different opt-in styles. They are lightweight, fast, and effective ways to build your audience.'
						)
				)
		);

		$rows[] = html(
			'div class="optin-enable" id="optin-select-popup"',
			html(
				'div class="cbox"',
				$this->input(
					array(
						'type' => 'checkbox',
						'name' => 'optins_popup_enable',
						'extra' => array( 'id' => 'optins_popup_enable' ),
					),
					$values
				),
				html( 'label', array( 'for' => 'optins_popup_enable' ), __( 'Enable Popups', 'Postmatic' ) )
			)
		);

		$rows[] = html(
			'div class="optin-enable gutter" id="optin-intro-popup"',
			html(
				'div',
					html( 'h2', __( 'Popup over the page <small>(click to enable)</small>', 'Postmatic' ) ),
					html( 'p',
						__(
							'A traditional popup which displays over the page content using an animated modal window. This popup can be triggered depending how long a user has been on the page, when the user scrolls to the bottom of the post, or after the user leaves a comment if you are using <a href="http://gopostmatic.com/epoch">Epoch</a>.'
						)
					)
				)
		);
		
		$rows[] = html(
			'div id="popup-options" class="gutter"',
			html( 'h3 class="col1"',  __( 'How would you like to trigger this popup?', 'Postmatic' ) ),
			html(
				'div id="popup-type" class="col1"',
				$this->input(
					array(
						'type' => 'radio',
						'name' => 'optins_popup_type',
						'choices' => Prompt_Optins::popup_bottom_trigger_options(),
					),
					$values
				),
				$this->input(
					array(
						'type' => 'checkbox',
						'name' => 'optins_popup_admin_test',
						'desc' => __( '<strong>Enable test mode</strong>: Always trigger popup for Administrator-level users', 'Postmatic' ),
					),
					$values
				)
			),
			html( 
				'div id="popup-time"',
				html(
					'h4 class="col1"',
					__( 
						'After how many seconds would you like this popup to pop?', 
						'Postmatic' 
					)
				),
				$this->input(
					array(
						'type' => 'number',
						'name' => 'optins_popup_time',
						'extra' => array( 'class' => 'col1' ),
					),
					$values
				)
			), 
			html(
				'div id="popup-title-text" class="col2"',
				html( 'h3',  __( 'Add a headline and some welcoming text.', 'Postmatic' ) ),
				html( 'h4', __( 'Headline', 'Postmatic' ) ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'optins_popup_title',
					),
					$values
				),
				html(
					'h4',
					__( 
						'Text to display below headline<small>Allowed HTML: a, strong, em, ul, ol, li, img, p, h2, h3, h4</small>', 
						'Postmatic'
					)
				),
				$this->input(
					array(
						'type' => 'textarea',
						'label' => 'd',
						'name' => 'optins_popup_desc',
					),
					$values

				)
			),
			$this->theme_chooser_html(
				'popup-theme',
				'optins_popup_theme',
				__( 'Choose a theme. This controls how the popup looks on your site.', 'Postmatic' ),
				$values
			),
			html(
				'div id="popup-image" class="optin-image"',
				html( 'h3',  __( 'Choose an image to display on the left side of your popup.', 'Postmatic' ) ),
					html( 'p',  
						sprintf( 
							__( 
								'We haven\'t built our image chooser yet but have created 8 different icons for you to use (or you can upload your own). <a href="%s" target="_blank">Download them here</a> and add the ones you like to your media library (below). In the future just search your media library for the word <em>Postmatic</em>.', 
								'Postmatic' 
							),
							Prompt_Core::$url_path . '/media/optins/postmatic-optin-icons.zip'
						)
					),
				html( 'img', array( 'src' => $popup_image->url() ) )
			),
			$this->input(
				array( 'name' => 'optins_popup_image', 'type' => 'hidden' ),
				$values
			),
			html(
				'input class="button" type="button" name="optins_popup_image_button"',
				array( 'value' => __( 'Change', 'Postmatic' ) )
			),
			html(
				'input class="button" type="button" name="optins_popup_image_reset_button"',
				array( 'value' => __( 'Reset', 'Postmatic' ) )
			)
		);

		$rows[] = html(
			'div class="optin-enable" id="optin-select-bottom"',
			html(
				'div class="cbox"',
				$this->input(
					array(
						'type' => 'checkbox',
						'name' => 'optins_bottom_enable',
						'value' => 1,
						'extra' => array( 'id' => 'optins_bottom_enable' )
					),
					$values
				),
				html( 'label', array( 'for' => 'optins_bottom_enable' ), __( 'Enable bottom slider', 'Postmatic' ) )
			)
		);
		
		$rows[] = html(
			'div class="optin-enable gutter" id="optin-intro-bottom"',
			html(
				'div',
					html( 'h2', __( 'Slide in from the bottom <small>(click to enable)</small>', 'Postmatic' ) ),
					html( 'p',
						__(
							'A subtle slider that pops up from the bottom of the browser window. This popup can be triggered depending how long a user has been on the page, when the user scrolls to the bottom of the post, or after the user leaves a comment if you are using <a href="http://gopostmatic.com/epoch">Epoch</a>.'
						)
					)
				)
		);

		$rows[] = html(
			'div id="bottom-options" class="gutter"',
			html( 'h3 class="col1"',  __( 'When should this slider trigger?', 'Postmatic' ) ),
			html(
				'div id="bottom-type" class="col1"',
				$this->input(
					array(
						'type' => 'radio',
						'name' => 'optins_bottom_type',
						'choices' => Prompt_Optins::popup_bottom_trigger_options(),
					),
					$values
				)
			),
			html( 
				'div id="bottom-time"',
				html (
					'h4 class="col1"',
					__( 'After how many seconds would you like the slider to slide?', 'Postmatic' )
				),
				$this->input(
					array(
						'type' => 'number',
						'name' => 'optins_bottom_time',
						'extra' => array( 'class' => 'col1' ),
					),
					$values
				)
			),
			html(
				'div id="bottom-title-desc" class="col2"',
				html( 'h3',  __( 'Add a headline and some welcoming text.', 'Postmatic' ) ),
				html( 'h4', __( 'Headline', 'Postmatic' ) ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'optins_bottom_title',
					),
					$values
				),
				html( 'h4', __( 'Text to display below headline<small>Allowed HTML: a, strong, em, ul, ol, li, img, p, h2, h3, h4</small>', 'Postmatic' ) ),
				$this->input(
					array(
						'type' => 'textarea',
						'label' => 'd',
						'name' => 'optins_bottom_desc',
					),
					$values
				)
			),
			$this->theme_chooser_html(
				'bottom-theme',
				'optins_bottom_theme',
				__( 'Choose a theme. This controls how the slider looks on your site.', 'Postmatic' ),
				$values
			),
			html(
				'div id="bottom-image" class="optin-image"',
				html( 'h3',  __( 'Choose an image to display on the right side of your popup.', 'Postmatic' ) ),
				html( 'p',  
					sprintf( 
						__( 
							'We haven\'t built our image chooser yet but have created 8 different icons for you to use (or you can upload your own). <a href="%s" target="_blank">Download them here</a> and add the ones you like to your media library (below). In the future just search your media library for the word <em>Postmatic</em>.', 
							'Postmatic' 
						),
						Prompt_Core::$url_path . '/media/optins/postmatic-optin-icons.zip'
					)
				),
				html( 'img', array( 'src' => $bottom_image->url() ) )
			),
			$this->input(
				array( 'name' => 'optins_bottom_image', 'type' => 'hidden' ),
				$values
			),
			html(
				'input class="button" type="button" name="optins_bottom_image_button"',
				array( 'value' => __( 'Change', 'Postmatic' ) )
			),
			html(
				'input class="button" type="button" name="optins_bottom_image_reset_button"',
				array( 'value' => __( 'Reset', 'Postmatic' ) )
			)
		);

		$rows[] = html(
			'div class="optin-enable" id="optin-select-topbar"',
			html(
				'div class="cbox"',
				$this->input(
					array(
						'type' => 'checkbox',
						'name' => 'optins_topbar_enable',
						'value' => '1',
						'extra' => array( 'id' => 'optins_topbar_enable' ),
					),
					$values
				),
				html( 'label', array( 'for' => 'optins_topbar_enable' ), __( 'Enable the topbar', 'Postmatic' ) )
			)
		);
		
		$rows[] = html(
			'div class="optin-enable gutter" id="optin-intro-topbar"',
			html(
				'div',
					html( 'h2', __( 'A bar across the top of your site <small>(click to enable)</small>', 'Postmatic' ) ),
					html( 'p',
						__(
							'A 50px tall bar that spans across the top of your site. The bar is a persistent (meaning it does no scroll with the rest of your content)  and unobtrusive reminder to subscribe. <strong>Note:</strong> may not render properly in all themes. This one is tricky. Give it a try to see.'
						)
					)
				)
		);

		$rows[] = html(
			'div id="topbar-title-desc" class="gutter"',
			html( 'h3 scope="row"',  __( 'What message should be in the bar?', 'Postmatic' ) ),
			html(
				'div',
				__( '', 'Postmatic' ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'optins_topbar_title',
					),
					$values
				)
			),
			$this->theme_chooser_html(
				'topbar-theme',
				'optins_topbar_theme',
				__( 'Choose a theme. This controls how the bar looks on your site.', 'Postmatic' ),
				$values
			)
		);

		$rows[] = html(
			'div class="optin-enable" id="optin-select-inline"',
			html(
				'div id="inpost-enable" class="cbox"',
				$this->input(
					array(
						'type' => 'checkbox',
						'name' => 'optins_inpost_enable',
						'value' => '1',
						'extra' => array( 'id' => 'optins_inpost_enable' ),
					),
					$values
				),
				html( 'label', array( 'for' => 'optins_inpost_enable' ), __( 'Enable after-the-post', 'Postmatic' ) )
			)
		);
		
		$rows[] = html(
			'div class="optin-enable gutter" id="optin-intro-inpost"',
			html(
				'div',
					html( 'h2', __( 'A form at the end of each post <small>(click to enable)</small>', 'Postmatic' ) ),
					html( 'p',
						__(
							'A nicely styled form that automatically displays at the bottom of each post on your site and invites users to subscribe. Classy, simple, and effective.'
						)
					)
				)
		);
		
		$rows[] = html(
				'div id="inpost-options" class="gutter"',
				html(
					'div id="inpost-title-desc"',
					html( 'h4', __( 'Give the box a headline', 'Postmatic' ) ),
					$this->input(
						array(
							'type' => 'text',
							'name' => 'optins_inpost_title',
						),
						$values
					),
					html(
						'h4',
						__( 
							'Add some text inviting your users to subscribe', 
							'Postmatic' 
						)
					),
					$this->input(
						array(
							'type' => 'textarea',
							'label' => 'd',
							'name' => 'optins_inpost_desc',
						),
						$values
					)
				),
				html(
					'div id="inpost-ids"',
					$this->input(
						array(
							'type' => 'text',
							'name' => 'optins_inpost_ids',
							'value' => 'all'
						),
						$values
	
					),
					__( 'IDs (comma separated) of posts to show in. Or use "all" to use on all posts.', 'Postmatic')
				),
				$this->theme_chooser_html(
					'inpost-theme',
					'optins_inpost_theme',
					__( 'Choose a theme. This controls how the form looks on your site.', 'Postmatic' ),
					$values
				),
				html(
					'div id="inpost-image" class="optin-image"',
					html( 'h3',  __( 
						'Choose an image to display on the left side of your form.', 
						'Postmatic' ) 
						),
					html( 'p',  
						sprintf( 
							__( 
								'We haven\'t built our image chooser yet but have created 8 different icons for you to use (or you can upload your own). <a href="%s" target="_blank">Download them here</a> and add the ones you like to your media library (below). In the future just search your media library for the word <em>Postmatic</em>.', 
								'Postmatic' 
							),
							Prompt_Core::$url_path . '/media/optins/postmatic-optin-icons.zip'
						)
					),
					html( 'img', array( 'src' => $inpost_image->url() ) )
				),
				$this->input(
					array( 'name' => 'optins_inpost_image', 'type' => 'hidden' ),
					$values
				),
				html(
					'input class="button" type="button" name="optins_inpost_image_button"',
					array( 'value' => __( 'Change', 'Postmatic' ) )
				),
				html(
					'input class="button" type="button" name="optins_inpost_image_reset_button"',
					array( 'value' => __( 'Reset', 'Postmatic' ) )
				)
		);


		$content = $this->table_wrap( implode( '', $rows ) );

		return $this->form_wrap( $content ) . $this->footer();
	}

	/**
	 * @since 1.4.0
	 *
	 * @param array $new_data
	 * @param array $old_data
	 * @return array
	 */
	function validate( $new_data, $old_data ) {

		$valid_data = $this->validate_checkbox_fields(
			$new_data,
			$old_data,
			array(
				'optins_popup_enable',
				'optins_popup_admin_test',
				'optins_bottom_enable',
				'optins_topbar_enable',
				'optins_inpost_enable'
			)
		);


		if ( isset( $new_data[ 'optins_popup_type' ] )  ) {
			if ( array_key_exists( $new_data[ 'optins_popup_type' ], Prompt_Optins::popup_bottom_trigger_options() ) ) {
				$valid_data[ 'optins_popup_type'  ] = $new_data[ 'optins_popup_type' ];
			}
		}

		if ( isset( $new_data[ 'optins_popup_time' ]  ) ) {
			$valid_data[ 'optins_popup_time'  ] = intval( $new_data['optins_popup_time'] );
		}

		if ( isset( $new_data[ 'optins_bottom_type' ] )  ) {
			if ( array_key_exists( $new_data[ 'optins_bottom_type' ], Prompt_Optins::popup_bottom_trigger_options() ) ) {
				$valid_data[ 'optins_bottom_type'  ] = $new_data[ 'optins_bottom_type' ];
			}
		}

		if ( isset( $new_data[ 'optins_bottom_time' ]  ) ) {
			$valid_data[ 'optins_bottom_time'  ] = intval( $new_data['optins_bottom_time'] );
		}

		$types = array_keys( Prompt_Optins::types() );
		$themes = array_keys( Prompt_Optins::themes() );
		foreach( $types as $type ) {
			$field = "optins_{$type}_theme";
			if ( isset ( $new_data[ $field ] ) ) {
				if ( in_array( $new_data[ $field ], $themes ) ) {
					$valid_data[ $field ] = $new_data[ $field ];
				}

			}

			$field = "optins_{$type}_title";
			if ( isset( $new_data[ $field ] ) ) {
				$valid_data[ $field ] = wp_strip_all_tags( $new_data[ $field ] );
			}

			$field = "optins_{$type}_desc";
			if ( isset( $new_data[ $field ] ) ) {
				$valid_data[ $field ] = balanceTags( strip_tags( $new_data[ $field ], '<a><strong><em><ul><ol><li><img><p><h2><h3><h4>' ) );
			}

			$field = "optins_{$type}_image";
			if ( isset( $new_data[ $field ] ) ) {
				$valid_data[ $field ] = intval( $new_data[ $field ] );
			}

		}

		return $valid_data;

	}

	/**
	 * @since 1.4.0
	 */
	protected function footer() {
		?>
			<script>
				jQuery( document ).ready( function ( $ ) {

					function open_media_frame( type, title ) {

						if ( ! wp.media.frames['prompt_optins_' + type] ) {
							wp.media.frames['prompt_optins_' + type] = wp.media( {
								title: title,
								multiple: false,
								library: { type: 'image' }
							} ).on( 'select', function() { set_image( type ); } );
						}

						wp.media.frames['prompt_optins_' + type].open();
					}

					function set_image( type ) {
						var attachment = wp.media.frames['prompt_optins_' + type].state().get( 'selection' ).first().toJSON();
						$( 'input[name="optins_' + type + '_image"]' ).val( attachment.id );
						$( '#' + type + '-image img' ).attr( {
							src: attachment.url,
							height: attachment.height,
							width: attachment.width
						} );
						$( 'input[name=optins_' + type + '_image_reset_button]' ).show();
					}

					function reset_image( type ) {
						$( '#' + type + '-image img' ).attr( {
							src: $( 'input[name="optins_default_image_url"]' ).val(), height: 500, width: 500
						} );
						$( 'input[name=optins_' + type + '_image]' ).val( '0' );
						$( 'input[name=optins_' + type + '_image_reset_button]' ).hide();
					}

					 //popup
					 function prompt_optins_admin_popup() {
						 if ( 'checked' == $( '[name="optins_popup_enable"]').attr('checked') ) {
						 	 $( '#optin-intro-popup' ).hide();
							 $( '#popup-options' ).show();
							 $( '#popup-theme' ).show();
							 $( '#popup-title-desc' ).show();
							 prompt_optin_admin_popup_type();
							 prompt_optin_admin_popup_image();
						 }else{
						 	 $( '#optin-intro-popup' ).show();
							 $( '#popup-title-desc' ).hide();
							 $( '#popup-options' ).hide();
							 $( '#popup-theme' ).hide();
						     $( '#optin-select-popups' ).show(); 
						 }
					 }

					function prompt_optin_admin_popup_type() {
						if ( 'timed' == $( 'input[name=optins_popup_type]:checked' ).val() ) {
							$( '#popup-time' ).show();
						}else{
							$( '#popup-time' ).hide();
						}
					}

					function prompt_optin_admin_popup_image() {
						if ( $( 'input[name=optins_popup_image]' ).val() === '0' ) {
							$( 'input[name=optins_popup_image_reset_button]' ).hide();
						}else{
							$( 'input[name=optins_popup_image_reset_button]' ).show();
						}
					}

					function open_popup_media_frame( e ) {
						e.preventDefault();
						open_media_frame( 'popup', '<?php _e( 'Choose a popup background image', 'Postmatic' ); ?>' );
					}

					prompt_optins_admin_popup();


					$( '[name="optins_popup_enable"]' ).change( function() {
						prompt_optins_admin_popup();
					});

					$( '[name="optins_popup_type"]' ).change( function() {
						prompt_optin_admin_popup_type();
					});

					$( '[name="optins_popup_image_button"]' ).click( open_popup_media_frame );

					$( '[name="optins_popup_image_reset_button"]' ).click( function( e ) {
						e.preventDefault();
						reset_image( 'popup' );
					} );

					//bottom slide-in
					function prompt_optins_admin_bottom() {
						if ( 'checked' == $( '[name="optins_bottom_enable"]').attr('checked') ) {
							$( '#bottom-options' ).show();
							$( '#bottom-title-desc' ).show();
							$( '#optin-intro-bottom' ).hide();
							prompt_optins_admin_bottom_type();
							prompt_optins_admin_bottom_image();
						}else{
							$( '#bottom-title-desc' ).hide();
							$( '#bottom-options' ).hide();
							$( '#optin-intro-bottom' ).show();
							
							
						}
					}

					function prompt_optins_admin_bottom_type() {
						if ( 'timed' == $( 'input[name=optins_bottom_type]:checked' ).val() ) {
							$( '#bottom-time' ).show();
						}else{
							$( '#bottom-time' ).hide();
						}
					}

					function prompt_optins_admin_bottom_image() {
						if ( $( 'input[name=optins_bottom_image]' ).val() === '0' ) {
							$( 'input[name=optins_bottom_image_reset_button]' ).hide();
						}else{
							$( 'input[name=optins_bottom_image_reset_button]' ).show();
						}
					}

					function open_bottom_media_frame( e ) {
						e.preventDefault();
						open_media_frame( 'bottom', '<?php _e( 'Choose a bottom background image', 'Postmatic' ); ?>' );
					}

					prompt_optins_admin_bottom();

					$( '[name="optins_bottom_enable"]' ).change( function() {
						prompt_optins_admin_bottom();
					});

					$( '[name="optins_bottom_type"]' ).change( function() {
						prompt_optins_admin_bottom_type();
					});

					$( '[name="optins_bottom_image_button"]' ).click( open_bottom_media_frame );

					$( '[name="optins_bottom_image_reset_button"]' ).click( function( e ) {
						e.preventDefault();
						reset_image( 'bottom' );
					} );

					//in post
					function prompt_optins_admin_inpost() {
						if ( 'checked' == $( '[name="optins_inpost_enable"]' ).attr('checked') ) {
							$( '#inpost-options' ).show();
							$( '#optin-intro-inpost' ).hide();
							prompt_optins_admin_inpost_image();
						}else{
							$( '#inpost-options' ).hide();
							$( '#optin-intro-inpost' ).show();
						}
					}

					function prompt_optins_admin_inpost_image() {
						if ( $( 'input[name=optins_inpost_image]' ).val() === '0' ) {
							$( 'input[name=optins_inpost_image_reset_button]' ).hide();
						}else{
							$( 'input[name=optins_inpost_image_reset_button]' ).show();
						}
					}

					function open_inpost_media_frame( e ) {
						e.preventDefault();
						open_media_frame( 'inpost', '<?php _e( 'Choose a inpost background image', 'Postmatic' ); ?>' );
					}

					prompt_optins_admin_inpost();

					$( '[name="optins_inpost_enable"]' ).change( function()  {
						prompt_optins_admin_inpost();
					});

					$( '[name="optins_inpost_image_button"]' ).click( open_inpost_media_frame );

					$( '[name="optins_inpost_image_reset_button"]' ).click( function( e ) {
						e.preventDefault();
						reset_image( 'inpost' );
					} );

					//topbar
					function prompt_optins_admin_topbar() {
						if ( 'checked' == $( '[name="optins_topbar_enable"]' ).attr('checked') ) {
							$( '#topbar-theme' ).show();
							$( '#topbar-title-desc' ).show();
							$( '#optin-intro-topbar' ).hide();
						}else{
							$( '#topbar-theme' ).hide();
							$( '#topbar-title-desc' ).hide();
							$( '#optin-intro-topbar' ).show();
						}
					}
					prompt_optins_admin_topbar();

					$( '[name="optins_topbar_enable"]' ).change( function() {
						prompt_optins_admin_topbar();
					});

					var $optin_enable_checkboxes = $( '.optin-enable' ).find( 'input[type=checkbox]' );
					$optin_enable_checkboxes.change( update_optin_selections );
					update_optin_selections();

					function update_optin_selections() {
						$optin_enable_checkboxes.parents( '.optin-enable' ).removeClass( 'active' );
						$optin_enable_checkboxes.filter( ':checked' ).parents( '.optin-enable' ).addClass( 'active' );
					}


				} );

			</script>

		<?php

	}

	/**
	 * @since 2.0.0
	 * @param string $id
	 * @param string $name
	 * @param string $prompt
	 * @param array|string $values
	 * @return string
	 */
	protected function theme_chooser_html( $id, $name, $prompt, $values ) {

		$radio_buttons = array();

		foreach (  Prompt_Optins::themes() as $slug => $label ) {

			$input_attributes = array(
				'type' => 'radio',
				'name' => $name,
				'value' => $slug,
			);

			$value = is_array( $values ) ? $values[$name] : $values;

			if ( $value === $slug ) {
				$input_attributes['checked'] = 'checked';
			}
			
			$radio_buttons[] = html(
				'label',
				array( 'class' => $slug ),
				html( 'input', $input_attributes ),
				$label
			);
		}

		return html (
			'div class="theme-chooser"',
			array( 'id' => $id ),
			html( 'h3', $prompt ),
			implode( '', $radio_buttons )
		);
	}

}