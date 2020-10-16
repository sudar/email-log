/* global ajaxurl, EmailLog */
( function( $ ) {

	$( document ).ready(function() {
		$( "#search_id-search-date-input" ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "yy-mm-dd"
		});

		$( document ).on("click", "#thickbox-footer-close", function( event ) {
			event.preventDefault();
			tb_remove();
		});

		// (Un)Star an Email Log.
		$( document ).on( "click", ".el-star-email", function( event ) {
			event.preventDefault();

			var icon = $( "span", this ),
				$this = $( this ),
				spinner = $this.next( 'img.el-star-spinner' );

			$.ajax( {
				url: ajaxurl,
				data: {
					"action": EmailLog.starEmailAction,
					"_wpnonce": EmailLog.starEmailNonce,
					"un_star": icon.hasClass( "dashicons-star-filled" ),
					"log_id": $this.data( "log-id" )
				},
				method: "POST",
				beforeSend: function () {
					spinner.show();
				},
				complete: function () {
					spinner.hide();
				}
			} )
				.success( function() {
					icon.toggleClass( "dashicons-star-empty" )
						.toggleClass( "dashicons-star-filled" );
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

		setTimeout( function () {
			var $openLogLink = $("#el-open-log-link");

			if ( $openLogLink ) {
				$openLogLink.trigger("click");
			}
		}, 2500);
	});

	var tabsInsertedEvent = "tabs_elem_inserted";

	insertionQ( "#tabs" ).every(function ( element ) {
		$( element ).trigger( tabsInsertedEvent )
	});

	$( document ).on( tabsInsertedEvent, function() {
		var activeTabIndex = parseInt( $( "#tabs ul" ).data( "active-tab" ) );

		activeTabIndex = isNaN( activeTabIndex ) ? 1 : activeTabIndex;
		$( "#tabs" ).tabs( { active: activeTabIndex } );
	} );

})( jQuery );
