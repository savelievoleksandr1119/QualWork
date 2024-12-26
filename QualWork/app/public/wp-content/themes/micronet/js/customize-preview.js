( function( $ ) {

	// Update the site title in real time...
	wp.customize( 'logo_width', function( value ) { 
		value.bind( function( newval ) {
        	document.querySelector('img.custom-logo').width=newval+'px';
        });
    } );
	
	
} )( jQuery );