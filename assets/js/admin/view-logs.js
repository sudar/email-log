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

		// Sent status tool tip.
		$( '.el-help' ).tooltip({
			content: function() {
				return $(this).prop('title');
			},
			position: {
				my: 'center top',
				at: 'center bottom+10',
				collision: 'flipfit'
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

	$( document ).on( tabsInsertedEvent, function () {
		$( '#tabs' ).tabs( { active: 1 } );
	});

})( jQuery );
