var prompt_options_page_env;

(function( $ ) {

	$( function() {

		if ( checking_connection() ) {
			return;
		}

		$('.nav-tab-wrapper .nav-tab').on('click', function( e ) {
			e.preventDefault();
			$( '.nav-tab-wrapper .nav-tab' ).removeClass( 'nav-tab-active' );
			$(e.currentTarget).addClass('nav-tab-active');
			var url_params = wpAjax.unserialize(e.target.href);
			var url = location.protocol + '//' + location.host + location.pathname;
			url += '?page=' + url_params.page + '&tab=' + url_params.tab;
			if (window.location.href != url) {
				history.pushState('', document.title, url);
			}

			// Show Tab content
			$( '.prompt-tab-content' ).addClass( 'hide' );
			$( '#' + url_params.tab ).removeClass( 'hide' ).addClass( 'show' );
		} );

		window.onpopstate = e => {
			__register_advanced_on_pop_state(e);
		};
		__register_advanced_on_pop_state( {} );

		function __register_advanced_on_pop_state( e ) {
			// Parse URL into an object with query params
			var url = wpAjax.unserialize(window.location.href);

			// If option in query var, click it
			if ('tab' in url) {
				$('*[data-tab-name="' + url.tab + '"]').trigger('click');
			} else {
				// We are on the main tab with no options - click it if not active
				var $first_item = $($('*[data-tab-name]')[0]);
				$first_item.removeClass( 'hide' ).addClass('show').trigger('click');
			}
		}


		$( '.wrap' ).show();

		// Add helpscout beacon
		!function(e,o,n){window.HSCW=o,window.HS=n,n.beacon=n.beacon||{};var t=n.beacon;t.userConfig={},t.readyQueue=[],t.config=function(e){this.userConfig=e},t.ready=function(e){this.readyQueue.push(e)},o.config={docs:{enabled:!0,baseUrl:"https://replyable.helpscoutdocs.com/"},contact:{enabled:!0,formId:"19014f8e-d8d5-11e6-8789-0a5fecc78a4d"}};var r=e.getElementsByTagName("script")[0],c=e.createElement("script");c.type="text/javascript",c.async=!0,c.src="https://djtflbt20bdde.cloudfront.net/",r.parentNode.insertBefore(c,r)}(document,window.HSCW||{},window.HS||{});

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
		init_helpscout_beacon();

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

}( jQuery ));
