jQuery(document).ready(function( $ ){
    $("open").click(function(){
        $("#postmatic-optin-topbar").effect("bounce","slow");
        $("open").slideUp()
    });

    $("close").click(function(){
        $("#postmatic-optin-topbar").slideUp();$("open").slideDown();
    });

    topbar = document.getElementById( 'wpadminbar' );
    if ( null != topbar ) {
        topbar_height = $( topbar ).height();
        $( '#postmatic-optin-topbar' ).css( 'top', topbar_height );
    }

});


  
