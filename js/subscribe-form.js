var prompt_subscribe_form_env;

jQuery(
	function ( $ ) {
		var $widgets = $( '.prompt-subscribe-widget-content' );
		var widget_promises = [];

		$widgets.each(
			function ( i, widget ) {
				var $widget = $( widget );
				$widget.attr( 'id', 'prompt-subscribe-widget-content-' + i );
				widget_promises.push( init( $widget ) );
			}
		);

		$.when.apply( undefined, widget_promises ).then( maybe_optins );

		function init( $widget ) {
			var $form,
				$message,
				$inputs,
				$expand_list,
				$subscriber_list,
				$loading_indicator,
				$nonce_input,
				$prompts,
				$submit_input,
				$mode_input,
				widget_id = $widget.attr( 'id' );

			return $.ajax(
				{
					url: prompt_subscribe_form_env.ajaxurl,
					method: 'GET',
					data: {
						action: 'prompt_subscribe_widget_content',
						widget_id: $widget.data( 'widgetId' ),
						template: $widget.data( 'template' ),
						collect_name: $widget.data( 'collectName' ),
						list_type: $widget.data( 'listType'),
						list_id: $widget.data( 'listId' )
					},
					success: load_form
				}
			);

			function load_form( content ) {

				// Some integrations, like OptInMonster, may have moved and invalidated our $widget reference
				var $widget = $( '#' + widget_id );

				$widget.html( content );
				$form = $widget.find( 'form.prompt-subscribe' );
				$message = $form.find( '.message' );
				$inputs = $form.find( '.inputs' );
				$expand_list = $form.find( '.expand-list' );
				$subscriber_list = $form.find( '.subscriber-list' );
				$loading_indicator = $form.find( '.loading-indicator' );
				$nonce_input = $form.find( 'input[name=subscribe_nonce]' );
				$prompts = $form.find( '.prompt' ).hide();
				$submit_input = $form.find( 'input[name=subscribe_submit]' );
				$mode_input = $form.find( 'input[name=mode]' );

				$nonce_input.val( prompt_subscribe_form_env.nonce );

				$prompts.filter( '.primary.subscribe' ).html( $widget.data( 'subscribePrompt' ) );
				$prompts.filter( '.' + $mode_input.val() ).show();

				enable_placeholders();

				$expand_list.click(
					function () {
						$subscriber_list.slideToggle();
					}
				);

				$form.submit( submit_form );
			}

			function enable_placeholders() {

				$( '[placeholder]' ).focus(
					function () {
						var $input = $( this );
						if ( $input.val() == $input.attr( 'placeholder' ) ) {
							$input.val( '' ).removeClass( 'placeholder' );
						}
					}
				).blur(
					function () {
						var $input = $( this );
						if ( $input.val() == '' || $input.val() === $input.attr( 'placeholder' ) ) {
							$input.addClass( 'placeholder' ).val( $input.attr( 'placeholder' ) );
						}
					}
				).blur().parents( 'form' ).submit(
					function () {
						$( this ).find( '[placeholder]' ).each(
							function () {
								var input = $( this );
								if ( input.val() == input.attr( 'placeholder' ) ) {
									input.val( '' );
								}
							}
						)
					}
				);
			}

			function submit_form( event ) {
				var $submitted_form = $( event.currentTarget );

				$loading_indicator.addClass( 'active' );
				$inputs.removeClass( 'active' );
				$message.removeClass( 'active' );
				$prompts.removeClass( 'active' );

				$.post(
					prompt_subscribe_form_env.ajaxurl, $submitted_form.serialize(), function ( message ) {

						$message.html( message ).addClass( 'active' );
						$loading_indicator.removeClass( 'active' );

					}
				).error(
					function () {

						$message.html( prompt_subscribe_form_env.ajax_error_message ).show();
						$inputs.addClass( 'active' );
						$prompts.addClass( 'active' );
						$loading_indicator.removeClass( 'active' );

					}
				);
				return false;
			}


		}

		function maybe_optins() {

			if ( 'object' == typeof postmatic_optin_options ) {

				$.each(
					postmatic_optin_options, function ( i, optin ) {
						var options = {};
						var bottom_id;
						var height;
						var width;

						if ( 'bottom' == optin.type ) {
							bottom_id = '#postmatic-bottom-optin-widget';
							height = $( bottom_id ).outerHeight() + 250;
							width = $( bottom_id ).outerWidth() + 150;
							options = {
								modal: 'postmatic-widget-bottom',
								content: bottom_id,
								autoload: false,
								height: height,
								width: "auto",
								sticky: "bottom right",
								title: optin.title,
								"focus": true,
								minimized: 7000
							};

							if ( 'bottom' == optin.trigger ) {
								var bottom_bottom_triggered = false;
								$( window ).scroll(
									function () {
										if ( false == bottom_bottom_triggered ) {
											if ( near_bottom() ) {
												bottom_bottom_triggered = true;
												$( '<div>' ).calderaModal( options );
											}
										}
									}
								);
							} else if ( 'comment' == optin.trigger ) {
								$( '#commentform' ).submit(
									function () {
										$( '<div>' ).calderaModal( options );
									}
								);
							} else {
								setTimeout(
									function () {
										$( '<div>' ).calderaModal( options );
									}, optin.trigger
								);

							}
						} else if ( 'popup' == optin.type ) {
							var popup;

							if ( has_seen( optin ) && ! optin.admin_test ) {
								return true;
							}

							bottom_id = '#postmatic-popup-optin-widget';
							height = $( bottom_id ).height() + 0;
							width = $( bottom_id ).outerWidth() + 150;
							options = {
								height: height,
								width: width,
								modal: 'postmatic-widget-popup',
								content: '#postmatic-popup-optin-widget',
								autoload: false,
								"focus": true
							};


							if ( 'bottom' == optin.trigger ) {
								var popup_bottom_triggered = false;
								$( window ).scroll(
									function () {
										if ( false == popup_bottom_triggered ) {
											if ( near_bottom() ) {
												will_see( optin );
												popup_bottom_triggered = true;
												popup = $( '<div>' ).calderaModal( options );
												shake_popup( popup );
											}
										}
									}
								);
							} else if ( 'comment' == optin.trigger ) {
								// Export this function so child frames can call it
								prompt_subscribe_form_env.popup_optin = function() {
									if ( has_seen( optin ) ) {
										return;
									}
									will_see( optin );
									popup = $( '<div>' ).calderaModal( options );
									shake_popup( popup );
								};
								$( '#commentform' ).submit( function() {
									setTimeout( prompt_subscribe_form_env.popup_optin, 3000 );
								} );
							} else {
								setTimeout(
									function () {
										will_see( optin );
										popup = $( '<div>' ).calderaModal( options );
										shake_popup( popup );

									}, optin.trigger
								);

							}


						}
					}
				);
			}

			function has_seen( optin ) {
				if ( optin.admin_test ) {
					return false;
				}
				var pattern = new RegExp( 'prompt_optin_' + optin.type + '=[^;]*' );
				return document.cookie.match( pattern );
			}

			function will_see( optin ) {
				document.cookie = 'prompt_optin_' + optin.type + '=1; path=/';
			}

			function near_bottom() {
				if ( $( '#comments' ).length > 0 ) {
					return near_comments();
				}
				return near_end();
			}

			function near_comments() {
				var $window = $( window );
				var doc_view_bottom = $window.scrollTop() + $window.height();
				var comments_top = $( '#comments' ).offset().top;
				return ( comments_top + 100 < doc_view_bottom );
			}

			function near_end() {
				var window_height = $( window ).height();
				var near_height = window_height * 0.15;
				var bottom_trigger = $( document ).height() - window_height - near_height;
				return $( window ).scrollTop() > bottom_trigger;
			}
		}

		function shake_popup( popup ) {

			var el = popup.modal;

			var interval = 100;
			var distance = 10;
			var times = 2;

			$( el ).css( 'position', 'relative' );

			for ( var iter = 0; iter < (times + 1); iter++ ) {
				$( el ).animate(
					{
						left: ((iter % 2 == 0 ? distance : distance * -1))
					}, interval
				);
			}//for

			$( el ).animate( {left: 0}, interval );

		}

	}
);
