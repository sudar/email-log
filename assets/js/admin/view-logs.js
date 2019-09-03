( function( $ ) {

	$( document ).ready(function() {
		$( '#search_id-search-date-input' ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});

		$( document ).on('click', '#thickbox-footer-close', function( event ) {
			event.preventDefault();
			tb_remove();
		});

		// Star an Email Log.
		$( document ).on( 'click', '.el-star-email', function( event ) {
			event.preventDefault();

			var nonceField = $( this ).data( 'nonce-field' );
			var isStarred = ! $( 'span', this ).hasClass( 'dashicons-star-filled' ) ? '1' : '0';
			var logId = $( this ).data( 'log-id' );
			var that = this;

			var data = {
				action: 'el-log-list-star-email',
				'nonce': elEmailLog.starActionNonce,
				'is_star': isStarred,
				'log_id': logId,
			};
			data[ nonceField ] = $( '#' + nonceField ).val() || '';

			$.ajax( {
				url: ajaxurl,
				data: data,
				method: 'POST',
			} )
				.complete( function() {
					$( 'span', that )
						.toggleClass( 'dashicons-star-empty' )
						.toggleClass( 'dashicons-star-filled' );
				} );


		} );
	} );

	var tabsInsertedEvent = 'tabs_elem_inserted';

	insertionQ( '#tabs' ).every(function ( element ) {
		$( element ).trigger( tabsInsertedEvent )
	});

	$( document ).on( tabsInsertedEvent, function () {
		$( '#tabs' ).tabs( { active: 1 } );
	});

})( jQuery );
