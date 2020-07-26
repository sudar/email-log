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

			var isStarred = ! $( 'span', this ).hasClass( 'dashicons-star-filled' ) ? '1' : '0';
			var logId = $( this ).data( 'log-id' );
			var that = this;

			var data = {
				'action': EmailLog.starEmailAction,
				'_wpnonce': EmailLog.starEmailNonce,
				'is_star': isStarred,
				'log_id': logId
			};

			$.ajax( {
				url: ajaxurl,
				data: data,
				method: 'POST',
			} )
				.success( function() {
					$( 'span', that )
						.toggleClass( 'dashicons-star-empty' )
						.toggleClass( 'dashicons-star-filled' );
				} );
		} );

		// Sent status tool tip.
		$( ".el-help" ).tooltip( {
			content: function() { return $( this ).prop( "title" ); },
			position: {
				my: "center top",
				at: "center bottom+10",
				collision: "flipfit"
			},
			hide: {
				duration: 100
			},
			show: {
				duration: 100
			}
		});
	});

	var tabsInsertedEvent = 'tabs_elem_inserted';

	insertionQ( '#tabs' ).every(function ( element ) {
		$( element ).trigger( tabsInsertedEvent )
	});

	$( document ).on( tabsInsertedEvent, function() {
		var activeTabIndex = parseInt( $( "#tabs ul" ).data( "active-tab" ) );

		activeTabIndex = isNaN( activeTabIndex ) ? 1 : activeTabIndex;
		$( "#tabs" ).tabs( { active: activeTabIndex } );
	} );

})( jQuery );
