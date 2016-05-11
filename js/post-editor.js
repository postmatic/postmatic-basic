var ajaxurl;

(function( $ ) {

	var post_id;
	$( document ).ready( function() { post_id = $( '#post_ID' ).val(); } );

	init_delivery_metabox();

	function init_delivery_metabox() {
		var $box, $status, $spinner, $preview_button, $no_featured_image_checkbox, $excerpt_only_checkbox;

		$( document ).ready( function() {

			$box = $( '#prompt_delivery' );
			$status = $box.find( '.status' );
			$spinner = $status.find( '.spinner' ).show().detach();
			$preview_button = $box.find( 'input[name="prompt_preview_email"]' )
				.on( 'click', send_preview_email );
			$no_featured_image_checkbox = $box.find( 'input[name=prompt_no_featured_image]' );
			$excerpt_only_checkbox = $box.find( 'input[name=prompt_excerpt_only]' );

			request_status();

		} );

		function request_status() {

			$status.append( $spinner );

			$.ajax( {
				url: ajaxurl,
				data: {
					action: 'prompt_post_delivery_status',
					post_id: post_id
				},
				dataType: 'json',
				success: update_status
			} );
		}

		function update_status( data ) {

			$status.html( data.description );

			if ( 'publish' == $( '#hidden_post_status' ).val() && data.sent_count < data.recipient_count ) {
				setTimeout( request_status, 10000 );
			}
		}

		function send_preview_email( e ) {
			e.preventDefault();

			$status.append( $spinner );
			$preview_button.attr( 'disable', true );

			data = {
				action: 'prompt_post_delivery_preview',
				post_id: post_id
			};

			if ( $no_featured_image_checkbox.is( ':checked' ) ) {
				data[$no_featured_image_checkbox.attr('name')] = $no_featured_image_checkbox.val();
			}

			if ( $excerpt_only_checkbox.is( ':checked' ) ) {
				data[$excerpt_only_checkbox.attr('name')] = $excerpt_only_checkbox.val();
			}

			$.ajax( {
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: show_preview_message
			} );
		}

		function show_preview_message( data ) {

			$spinner.hide();
			$preview_button.attr( 'disable', false );

			$updated = $('<div class="updated"></div>' ).html( data.message );
			$status.append( $updated );
			$updated.fadeOut( 5000 );

		}

	}


}( jQuery ) );