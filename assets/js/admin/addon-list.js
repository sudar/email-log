/**
 * Show/Hide individual add-on license key input.
 */
( function( $ ) {
	$( document ).ready( function() {
		$( '.el-addon .el-expander' ).on( 'click', function() {
			var $this = $( this );

			$this.parent().find( '.individual-license' ).toggle();

			if ( $this.hasClass( 'dashicons-arrow-down' ) ) {
				$this
					.removeClass( 'dashicons-arrow-down' )
					.addClass( 'dashicons-arrow-up' );
			} else {
				$this
					.removeClass( 'dashicons-arrow-up' )
					.addClass( 'dashicons-arrow-down' );
			}
		} );
	} );
} )(jQuery);
