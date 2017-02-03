( function( $ ) {

    $( document ).ready(function() {
        $( '#search_id-search-date-input' ).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd'
        });
    });

})( jQuery );