var prompt_admin_users_env;

jQuery( function( $ ) {
	$( '#wpbody-content' ).append(
		$( '<a></a>' )
			.attr( 'href', prompt_admin_users_env.export_url )
			.attr( 'class', 'button' )
			.text( prompt_admin_users_env.export_label )
	);
} );