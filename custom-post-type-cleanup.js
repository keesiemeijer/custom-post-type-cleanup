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

		$( "input[name='custom_post_type_cleanup']" ).on( "click", function( e ) {

			if ( ( 'localStorage' in window ) && ( window[ 'localStorage' ] !== null ) ) {

				var post_type = $( "#cptc_post_type" ).val();
				var storage = localStorage.getItem( "cptc_run_delete_dialog" );

				if ( post_type.length && ( post_type !== storage ) ) {

					// Remove confirm dialog after first delete terms dialog.
					localStorage.setItem( "cptc_run_delete_dialog", post_type );

					var confirm_msg = cptc_plugin.confirm.replace( /%s/g, post_type );

					return confirm( confirm_msg );
				}
			}

		} );
	} );

} )( jQuery );