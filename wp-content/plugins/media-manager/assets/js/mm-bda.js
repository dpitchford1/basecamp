/* ==========================================================================
   mm-bda.js  —  Media Manager: Block Direct Access page
   Phase 12. Depends on: jQuery, mm_ajax.
   ========================================================================== */

( function ( $, mm ) {
	'use strict';

	$( document ).ready( function () {
		if ( ! $( '#mm-bda-wrap' ).length ) { return; }

		loadProtectedFolders();
		loadBlockedIps();
		bindEvents();
	} );

	/* -----------------------------------------------------------------------
	   Protected folders
	----------------------------------------------------------------------- */

	/**
	 * Fetch the current list of BDA-protected folders from the server and render the table.
	 *
	 * @return {void}
	 */
	function loadProtectedFolders() {
		$.post( mm.url, { action: 'mm_get_protected_files', nonce: mm.nonce }, function ( r ) {
			if ( ! r.success ) { return; }
			renderFolderTable( r.data.folders );
		} );
	}

	/**
	 * Render the protected-folders table rows.
	 *
	 * @param  {Array<{folder_id: number, folder_path: string, protected_at: string}>} rows  Folder data from the server.
	 * @return {void}
	 */
	function renderFolderTable( rows ) {
		var $tbody = $( '#mm-protected-folders tbody' ).empty();

		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + t( 'no_protected', 'No folders protected.' ) + '</td></tr>' );
			return;
		}

		$.each( rows, function ( _, row ) {
			$tbody.append(
				'<tr data-folder-id="' + row.folder_id + '">' +
				'<td>' + escHtml( row.folder_path ) + '</td>' +
				'<td>' + escHtml( row.protected_at ) + '</td>' +
				'<td><button type="button" class="button button-small mm-unprotect-folder">' + t( 'unprotect', 'Remove' ) + '</button></td>' +
				'</tr>'
			);
		} );
	}

	/* -----------------------------------------------------------------------
	   Blocked IPs
	----------------------------------------------------------------------- */

	/**
	 * Fetch the current blocked-IP list from the server and render the table.
	 *
	 * @return {void}
	 */
	function loadBlockedIps() {
		$.post( mm.url, { action: 'mm_get_blocked_ips', nonce: mm.nonce }, function ( r ) {
			if ( r.success ) { renderIpTable( r.data ); }
		} );
	}

	/**
	 * Render the blocked-IP table rows.
	 *
	 * @param  {Array<{id: number, ip_address: string, created_at: string}>} rows  IP records from the server.
	 * @return {void}
	 */
	function renderIpTable( rows ) {
		var $tbody = $( '#mm-ip-table tbody' ).empty();

		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + t( 'no_blocked', 'No blocked IPs.' ) + '</td></tr>' );
			return;
		}

		$.each( rows, function ( _, row ) {
			$tbody.append(
				'<tr>' +
				'<td><input type="checkbox" class="mm-ip-check" value="' + row.id + '"></td>' +
				'<td>' + escHtml( row.ip_address ) + '</td>' +
				'<td>' + escHtml( row.created_at ) + '</td>' +
				'</tr>'
			);
		} );
	}

	/* -----------------------------------------------------------------------
	   Events
	----------------------------------------------------------------------- */

	/**
	 * Attach all event listeners for the Security page.
	 *
	 * @return {void}
	 */
	function bindEvents() {

		// Remove protected folder.
		$( document ).on( 'click', '.mm-unprotect-folder', function () {
			var folderId = $( this ).closest( 'tr' ).data( 'folder-id' );
			$.post( mm.url, {
				action:    'mm_toggle_file_access',
				nonce:     mm.nonce,
				folder_id: folderId,
				protect:   0,
			}, function () { loadProtectedFolders(); } );
		} );

		// Add blocked IP.
		$( document ).on( 'click', '#mm-add-ip', function () {
			var ip = $( '#mm-ip-address' ).val().trim();
			if ( ! ip ) { return; }
			$.post( mm.url, {
				action:     'mm_add_blocked_ip',
				nonce:      mm.nonce,
				ip_address: ip,
			}, function ( r ) {
				if ( r.success ) {
					renderIpTable( r.data );
					$( '#mm-ip-address' ).val( '' );
				} else {
					window.alert( ( r.data && r.data.message ) || t( 'error_add_ip', 'Could not add IP.' ) );
				}
			} );
		} );

		// Remove selected IPs.
		$( document ).on( 'click', '#mm-remove-ips', function () {
			var ids = $( '.mm-ip-check:checked' ).map( function () {
				return parseInt( $( this ).val(), 10 );
			} ).get();
			if ( ! ids.length ) { return; }
			$.post( mm.url, {
				action: 'mm_remove_blocked_ips',
				nonce:  mm.nonce,
				ids:    ids,
			}, function ( r ) {
				if ( r.success ) { renderIpTable( r.data ); }
			} );
		} ); 

		// Save BDA settings form.
		$( document ).on( 'submit', '#mm-bda-settings-form', function ( e ) {
			e.preventDefault();
			var data = $( this ).serializeArray();
			data.push( { name: 'action', value: 'mm_save_bda_settings' } );
			data.push( { name: 'nonce',  value: mm.nonce } );
			$.post( mm.url, data, function ( r ) {
				if ( r.success ) {
					showNotice( t( 'settings_saved', 'Settings saved.' ), 'success' );
					$( 'html, body' ).animate( { scrollTop: 0 }, 300 );
				} else {
					showNotice( ( r.data && r.data.message ) || t( 'save_error', 'Save failed.' ), 'error' );
				}
			} );
		} );

		// Bottom save button — triggers form submit handler above.
		$( document ).on( 'click', '#mm-bda-save-btn', function () {
			$( '#mm-bda-settings-form' ).trigger( 'submit' );
		} );

		// IP table select-all toggle.
		$( document ).on( 'change', '#mm-ip-select-all', function () {
			$( '.mm-ip-check' ).prop( 'checked', $( this ).prop( 'checked' ) );
		} );
	}

	/* -----------------------------------------------------------------------
	   Utilities
	----------------------------------------------------------------------- */

	/**
	 * Prepend a transient notice banner to the BDA page wrap.
	 *
	 * @param  {string} message  Text to display.
	 * @param  {string} type     Notice type: 'success' or 'error'.
	 * @return {void}
	 */
	function showNotice( message, type ) {
		var $n = $( '<div class="mm-notice mm-notice-' + type + '">' + escHtml( message ) + '</div>' );
		$( '#mm-bda-wrap' ).prepend( $n );
		setTimeout( function () { $n.fadeOut( 400, function () { $( this ).remove(); } ); }, 4000 );
	}

	/**
	 * Return a translated string, falling back to the provided default.
	 *
	 * @param  {string} key      i18n key.
	 * @param  {string} fallback Default text.
	 * @return {string}
	 */
	function t( key, fallback ) {
		return ( mm.i18n && mm.i18n[ key ] ) ? mm.i18n[ key ] : fallback;
	}

	/**
	 * Escape a value for safe insertion as HTML text content.
	 *
	 * @param  {*}      str  Value to escape.
	 * @return {string}
	 */
	function escHtml( str ) {
		return $( '<span>' ).text( String( str ) ).html();
	}

}( jQuery, window.mm_ajax || {} ) );
