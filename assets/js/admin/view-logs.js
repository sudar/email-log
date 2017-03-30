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
	});

	var tabsInsertedEvent = 'tabs_elem_inserted';

	insertionQ( '#tabs' ).every(function ( element ) {
		$( element ).trigger( tabsInsertedEvent )
	});

	$( document ).on( tabsInsertedEvent, function() {
		$( '#tabs' ).tabs();
	});


})( jQuery );