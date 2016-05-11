var window, prompt_html_metabox_env;

(function( $ ) {

	$( document ).ready( init_text_metabox );

	function init_text_metabox() {
		var $customize_button = $( 'input.prompt-customize-html' ).on( 'click', confirm ),
			$editor = $( '#prompt_custom_html_editor' ).hide();

		if ( $customize_button.length === 0 ) {
			customize();
		}
		
		function confirm( e ) {
			e.preventDefault();
			if ( window.confirm( prompt_html_metabox_env.confirm_prompt ) ) {
				customize();
			}
		}
		
		function customize() {
			$( 'input[name="' + prompt_html_metabox_env.enable_custom_html_name + '"]' ).val( true );
			$editor.show();
			$customize_button.hide();
		}
	}

}( jQuery ) );
