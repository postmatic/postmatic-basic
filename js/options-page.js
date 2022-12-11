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
		!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
		window.Beacon('init', 'e4247595-68e9-4d11-b901-c438a539e627')

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
