
var prompt_comment_form_env;

jQuery( function( $ ) {

	var $subscribe_area = $( '#prompt-comment-subscribe' );

	if ( $subscribe_area.length > 0 ) {
		move_subscribe_area_above_submit();
	}

	var $panel = $( '.prompt-unsubscribe' );
	var $loading_indicator = $panel.find( '.loading-indicator' ).detach().show();
	var $unsubscribe_button = $panel.find( 'input[name=' + prompt_comment_form_env.action + ']' );

	$unsubscribe_button.on( 'click', unsubscribe );

	if ( window.parent ) {
		$( '#commentform' ).submit( notify_parent_of_submit )
	}

	function unsubscribe( e ) {
		e.preventDefault();

		$panel.empty().append( $loading_indicator );

		$.post(
			prompt_comment_form_env.url,
			prompt_comment_form_env,
			render_result
		)
	}

	function render_result( content ) {
		$panel.html( content );
	}

	function move_subscribe_area_above_submit() {

		var $form = $subscribe_area.parents( 'form' );

		if ( $form.length === 0 ) {
			return;
		}

		var $submit = $form.find( 'input[type="submit"]' );

		if ( $submit.length === 0 ) {
			return;
		}

		var $submit_area = $submit.parent( 'p,div' );

		if ( $submit_area.length === 0 ) {
			$submit_area = $submit;
		}

		$submit_area.before( $subscribe_area );
	}

	function notify_parent_of_submit() {

		var subscribe_form_env = window.parent.prompt_subscribe_form_env;

		if ( ! subscribe_form_env || ! subscribe_form_env.popup_optin ) {
			return;
		}

		setTimeout( subscribe_form_env.popup_optin, 3000 );
	}
} );