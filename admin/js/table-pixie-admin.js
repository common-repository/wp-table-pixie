(function( $ ) {
	'use strict';

	/**
	 * Event Handlers.
	 */
	function toggleTruncate( event ) {
		event.preventDefault();
		var nonce = $( '#_table_pixie_nonce' ).val();
		var blogId = $( '#current-blog-id' ).val();
		var table = $( '#current-table' ).val();
		var collapsed = $( this ).hasClass( 'collapsed' );
		var column = event.data.column;
		var cell = $( this ).parents( 'td' );
		var tableId = cell.siblings( '.column-__tp_id' ).text();

		$.post(
			ajaxurl,
			{
				action: 'table_pixie_toggle_truncate',
				nonce: nonce,
				blog_id: blogId,
				table: table,
				__tp_id: tableId,
				column: column,
				collapsed: collapsed
			},
			function( response ) {
				cell.html( response );
			}
		)
	}

	function toggleRememberSearch( event ) {
		event.preventDefault();
		var nonce = $( '#_table_pixie_nonce' ).val();
		var rememberSearch = $( this ).prop( "checked" );

		$.post(
			ajaxurl,
			{
				action: 'table_pixie_toggle_remember_search',
				nonce: nonce,
				remember_search: rememberSearch
			}
		)
	}

	function toggleExpandArray( event ) {
		event.preventDefault();
		var siblingArray = $( this ).siblings( 'dl.array' );
		var collapsed = $( siblingArray ).hasClass( 'hidden' );

		expandArray( this, collapsed );
	}

	function expandArray( element, expand ) {
		var siblingArray = $( element ).siblings( 'dl.array' );

		if ( expand ) {
			$( element ).children( '.dashicons.collapsed' ).addClass( 'hidden' );
			$( element ).children( '.dashicons.expanded' ).removeClass( 'hidden' );
			siblingArray.removeClass( 'hidden' );
		} else {
			$( element ).children( '.dashicons.expanded' ).addClass( 'hidden' );
			$( element ).children( '.dashicons.collapsed' ).removeClass( 'hidden' );
			siblingArray.addClass( 'hidden' );
		}
	}

	function expandAll( event ) {
		event.preventDefault();
		var richViewContainer = $( this ).parents( 'td' );
		richViewContainer.find( '.array.count' ).each( function() {
			expandArray( this, event.data.expand );
		} );
	}

	$( function() {
		$( 'body' ).on( 'change', '#remember-search', toggleRememberSearch );
		$( '.wp-list-table' ).on( 'click', 'td.column-meta_value a.truncate', { column: "meta_value" }, toggleTruncate );
		$( '.wp-list-table' ).on( 'click', 'td.column-meta_value a.expand-all', { expand: true }, expandAll );
		$( '.wp-list-table' ).on( 'click', 'td.column-meta_value a.collapse-all', { expand: false }, expandAll );
		$( '.wp-list-table' ).on( 'click', 'td.column-meta_value dl dd.value span.array', toggleExpandArray );
	} );
})( jQuery );
