( function( $ ) {

	$( document ).ready( function() {

		if ( typeof cptc_plugin === 'undefined' ) {
			return;
		}

		if ( !$( cptc_plugin ).length ) {
			return;
		}

		if ( cptc_plugin.remove_storage ) {
			localStorage.removeItem( "cptc_run_delete_dialog" );
		}

		$( "input[name='cptc_delete']" ).on( "click", function( e ) {

			if ( ( 'localStorage' in window ) && ( window[ 'localStorage' ] !== null ) ) {

				var post_type = $( "#cptc_post_type" ).val();
				var storage = localStorage.getItem( "cptc_run_delete_dialog" );

				if ( post_type.length && ( post_type !== storage ) ) {
									
					var confirm_msg = cptc_plugin.confirm.replace( /%s/g, post_type );
					var reply       = confirm( confirm_msg );

					if ( reply == true ) {
						localStorage.setItem( "cptc_run_delete_dialog", post_type );
						return confirm;
					} else {
						localStorage.removeItem( "cptc_run_delete_dialog" );
						return false;
					}
				}
			}

		} );
	} );

} )( jQuery );