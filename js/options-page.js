var prompt_options_page_env;

(function( $ ) {

	$( function() {

		if ( checking_connection() ) {
			return;
		}

		$( '#prompt-tabs' ).tabs()
				.addClass( "ui-tabs-vertical ui-helper-clearfix" )
				.find( 'li' )
				.removeClass( "ui-corner-top" )
				.addClass( "ui-corner-left" );

		$( '.wrap' ).show();

		// Add helpscout beacon
		!function(e,o,n){window.HSCW=o,window.HS=n,n.beacon=n.beacon||{};var t=n.beacon;t.userConfig={},t.readyQueue=[],t.config=function(e){this.userConfig=e},t.ready=function(e){this.readyQueue.push(e)},o.config={docs:{enabled:!0,baseUrl:"https://postmatic.helpscoutdocs.com/"},contact:{enabled:!0,formId:"0eebf042-62db-11e5-8846-0e599dc12a51"}};var r=e.getElementsByTagName("script")[0],c=e.createElement("script");c.type="text/javascript",c.async=!0,c.src="https://djtflbt20bdde.cloudfront.net/",r.parentNode.insertBefore(c,r)}(document,window.HSCW||{},window.HS||{});

		$( 'input.last-submit' ).keypress( function( e ) {
			var $form = $( this ).parents( 'form' );
			if ( ( e.keyCode && e.keyCode === 13 ) || ( e.which && e.which === 13 ) ) {
				e.preventDefault();
				$form.find( 'input[type="submit"]' ).get( -1 ).click();
			}
		} );

		$( 'input.no-submit' ).keypress( function( e ) {
			if ( ( e.keyCode && e.keyCode === 13 ) || ( e.which && e.which === 13 ) ) {
				$( this ).select();
				return false;
			}
		} );

		$( 'form' ).submit( disable_next_submit );

		init_download_prompt();
		init_core_tab();
		init_import_tab();
		init_helpscout_beacon();
		init_mailchimp_import();

	} );

	function disable_next_submit() {
		$(this).submit( function() {
			return false;
		} );
		return true;
	}

	function checking_connection() {
		var poll_count = 0;
		var $checking_connection = $( '#checking-connection' );
		$checking_connection.find( '.spinner' ).show();

		if ( $checking_connection.length === 0 ) {
			return false;
		}

		var interval = setInterval( poll, 3000 );

		function poll() {

			poll_count++;

			if ( poll_count > 10 ) {
				clearInterval( interval );
				fail();
				return;
			}

			$.ajax( {
				url: ajaxurl,
				data: { action: 'prompt_is_connected' },
				success: update
			} );
		}

		function update( data ) {
			if ( data.data ) {
				window.location.reload( true );
			}
		}

		function fail() {
			$checking_connection.hide();
			$( '#bad-connection' ).show();
		}
	}

	function init_download_prompt() {

		if ( $( '#download-modal' ).length === 0 ) {
			return;
		}

		$( '#prompt-tabs' ).find( 'a.download-modal' ).click( function( e ) {
			e.preventDefault();
			show();
		} );

		if ( !prompt_options_page_env.skip_download_intro ) {
			show();
		}

		function show() {

			tb_show( prompt_options_page_env.download_title, '#TB_inline?inlineId=download-modal' );
			setTimeout( function() { $(window).trigger( 'resize' ); }, 1 );

			var $download_prompt = $( '#download-premium-prompt, #download-labs-prompt' );

			$download_prompt.find( 'a.download' ).click( function() {
				$download_prompt.hide();
				$( '#install-labs-prompt, #install-premium-prompt' ).show();
			} );

			$( '#dismiss-download-modal' ).click( function( e ) {

				e.preventDefault();

				$.ajax( {
					url: ajaxurl,
					data: { action: 'prompt_dismiss_notice', 'class': 'Prompt_Admin_Download_Modal_Notice' },
					success: tb_remove
				} );
			} );

		}
	}

	function init_core_tab() {

		var $form = $( '#prompt-settings-core' ).find( 'form' );
		var modules = [
			'invites',
			'optins',
			'mailchimp-import',
			'jetpack-import',
			'mailpoet-import',
			'post-delivery',
			'digests',
			'comment-delivery',
			'skimlinks',
			'buy-sell-ads',
			'webhooks',
			'notes',
			'analytics'
		];
		var template_module_checkbox_selectors = [
			'input[name="enable_invites"]',
			'input[name="enable_post_delivery"]',
			'input[name="enable_digests"]',
			'input[name="enable_comment_delivery"]'
		];
		var $template_tab = $( '#prompt-tab-your-template' );
		var $template_module_checkboxes = $form.find( template_module_checkbox_selectors.join( ',' ) );

		$.each( modules, function( index, module ) {
			var $tab = $( '#prompt-tab-' + module );
			var module_name = module.replace( /-/g, '_' );
			var $checkbox = $form.find( 'input[name="enable_' + module_name + '"]' ).on( 'change', function() {
				toggle_tab( $( this ), $tab );
				maybe_toggle_template_tab();
			} );
			toggle_tab( $checkbox, $tab );
			maybe_toggle_template_tab();
		} );

		function toggle_tab( $checkbox, $tab ) {

			if ( $checkbox.is( ':checked' ) && !$tab.is( ':visible' ) ) {
				save();
				$tab.fadeIn( 'slow' );
				return;
			}

			if ( !$checkbox.is( ':checked' ) && $tab.is( ':visible' ) ) {
				save();
				$tab.fadeOut( 'slow' );
			}
		}

		function maybe_toggle_template_tab() {
			if ( $template_tab.is( ':visible' ) && $template_module_checkboxes.filter( ':checked' ).length === 0 ) {
				$template_tab.fadeOut( 'slow' );
			}
			if ( !$template_tab.is( ':visible' ) && $template_module_checkboxes.filter( ':checked' ).length > 0 ) {
				$template_tab.fadeIn( 'slow' );
			}
		}

		function save() {
			$.post( location.href, $form.serialize() );
		}
	}

	function init_import_tab() {
		var $rejected_addresses_input = $( 'input[name="rejected_addresses"]');

		$rejected_addresses_input.click( invite_rejected_addresses );

		function invite_rejected_addresses( e ) {
			e.preventDefault();

			$( 'textarea[name="manual_addresses"]' )
				.val( $rejected_addresses_input.data( 'addresses' ) )
				.trigger( 'keyup' );

			$( 'a[href="#prompt-settings-invites"]' ).click();
		}
	}

	function init_helpscout_beacon() {
		HS.beacon.config({
				modal: false,
				topArticles: true,
				color: '#DE4F0F',
				icon: 'question',
				attachment: true,
				poweredBy: false
		});
	}

	function init_mailchimp_import() {
		var $submit 		= $('#mailchimp_import_submit' ).hide();

		$(document).on('click', '#mail_chimp_load_lists', load_lists );

		function load_lists( e ){

			e.preventDefault();

			var $container		= $('#mailchimp_lists'),
				$spinner		= $('#mail_chimp_spinner'),
				$api_key_input  = $('#mailchimp_api_key' );

			data = {
				action	: 'prompt_mailchimp_get_lists',
				api_key	: $api_key_input.val()
			};

			$submit.hide();
			$container.empty();
			$spinner.show();

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: data,
				dataType: 'json',
				complete: function(){
					$spinner.hide();
				},
				success: function( data ){
					if( false === data.success ){
						$container.html( '<div class="error"><p>' + data.data.error + '.</p></div>' );
					} else {
						$container.html( data.data );
						if ( $( 'select[name="signup_list_index"] option' ).length > 1 ) {
							$( '#signup_list_index_label' ).show();
						}
						$submit.show();
					}
				}
			} );
		}
	}

}( jQuery ));
