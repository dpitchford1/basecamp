/* ==========================================================================
   mm-upload.js  —  Media Manager: drag-drop / browse file upload
   Phase 6. Depends on: jQuery, mm_ajax (localised by Assets).
   ========================================================================== */

( function ( $, mm ) {
	'use strict';

	var activeFolderId = 0;

	/* -----------------------------------------------------------------------
	   Init — called once Library page is ready
	----------------------------------------------------------------------- */

	$( document ).ready( function () {
		if ( ! $( '#mm-upload-btn' ).length ) { return; }

		// Inject the upload panel HTML if not already in the DOM.
		if ( ! $( '#mm-upload-panel' ).length ) {
			$( '#mm-content-pane' ).prepend( buildPanel() );
		}

		bindEvents();
	} );

	/* -----------------------------------------------------------------------
	   Panel HTML
	----------------------------------------------------------------------- */

	function buildPanel() {
		return [
			'<div id="mm-upload-panel">',
			'  <div id="mm-drop-zone">',
			'    <p>' + t( 'drop_files', 'Drop files here or' ) + ' ' +
					'<button type="button" id="mm-browse-btn" class="button">' + t( 'browse', 'Browse' ) + '</button></p>',
			'    <input type="file" id="mm-file-input" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">',
			'  </div>',
			'  <div id="mm-upload-progress"></div>',
			'</div>',
		].join( '\n' );
	}

	/* -----------------------------------------------------------------------
	   Events
	----------------------------------------------------------------------- */

	function bindEvents() {
		// Toggle panel.
		$( document ).on( 'click', '#mm-upload-btn', function () {
			$( '#mm-upload-panel' ).toggleClass( 'mm-open' );
		} );

		// Browse button.
		$( document ).on( 'click', '#mm-browse-btn', function () {
			$( '#mm-file-input' ).trigger( 'click' );
		} );

		// File input change.
		$( document ).on( 'change', '#mm-file-input', function () {
			handleFiles( this.files );
			this.value = '';   // allow re-selecting same file
		} );

		// Drag events on drop zone.
		$( document ).on( 'dragover dragenter', '#mm-drop-zone', function ( e ) {
			e.preventDefault();
			$( '#mm-drop-zone' ).addClass( 'mm-drag-over' );
		} );

		$( document ).on( 'dragleave drop', '#mm-drop-zone', function ( e ) {
			e.preventDefault();
			$( '#mm-drop-zone' ).removeClass( 'mm-drag-over' );
			if ( 'drop' === e.type && e.originalEvent.dataTransfer ) {
				handleFiles( e.originalEvent.dataTransfer.files );
			}
		} );

		// Listen for active folder changes (fired by mm-library.js).
		$( document ).on( 'mm:folder-selected', function ( _e, folderId ) {
			activeFolderId = folderId;
		} );
	}

	/* -----------------------------------------------------------------------
	   Upload queue
	----------------------------------------------------------------------- */

	function handleFiles( files ) {
		if ( ! files || ! files.length ) { return; }

		if ( ! activeFolderId ) {
			showNotice( t( 'select_folder_first', 'Please select a folder before uploading.' ), 'error' );
			return;
		}

		var $progress = $( '#mm-upload-progress' ).empty();

		for ( var i = 0; i < files.length; i++ ) {
			uploadFile( files[ i ], $progress );
		}
	}

	function uploadFile( file, $progress ) {
		var $item  = $( buildProgressItem( file.name ) ).appendTo( $progress );
		var $bar   = $item.find( '.mm-progress-bar' );
		var data   = new FormData();

		data.append( 'action',    'mm_upload_file' );
		data.append( 'nonce',     mm.nonce );
		data.append( 'folder_id', activeFolderId );
		data.append( 'file',      file );

		$.ajax( {
			url:         mm.url,
			type:        'POST',
			data:        data,
			processData: false,
			contentType: false,
			xhr: function () {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener( 'progress', function ( e ) {
					if ( e.lengthComputable ) {
						$bar.css( 'width', Math.round( e.loaded / e.total * 100 ) + '%' );
					}
				}, false );
				return xhr;
			},
			success: function ( response ) {
				if ( response && response.success ) {
					$item.addClass( 'mm-upload-done' );
					$bar.css( 'width', '100%' );
					$( document ).trigger( 'mm:file-uploaded', [ response.data ] );
				} else {
					$item.addClass( 'mm-upload-error' );
					var msg = ( response && response.data && response.data.message )
						? response.data.message
						: ( JSON.stringify( response ) || t( 'upload_failed', 'Upload failed.' ) );
					$item.find( '.mm-upload-name' ).append( ' — ' + escHtml( msg ) );
				}
			},
			error: function ( xhr ) {
				$item.addClass( 'mm-upload-error' );
				var body = xhr.responseText || '';
				var hint = body ? escHtml( body.substring( 0, 200 ) ) : 'HTTP ' + xhr.status + ' (no response body — possible PHP upload size limit)';
				$item.find( '.mm-upload-name' ).append( ' — ' + hint );
			},
		} );
	}

	function buildProgressItem( filename ) {
		return '<div class="mm-upload-item">' +
			'<span class="mm-upload-name">' + escHtml( filename ) + '</span>' +
			'<div class="mm-progress-bar-wrap"><div class="mm-progress-bar" style="width:0%"></div></div>' +
		'</div>';
	}

	/* -----------------------------------------------------------------------
	   Utilities
	----------------------------------------------------------------------- */

	function showNotice( message, type ) {
		var $notice = $( '<div class="mm-notice mm-notice-' + type + '">' + escHtml( message ) + '</div>' );
		$( '#mm-upload-panel' ).prepend( $notice );
		setTimeout( function () { $notice.fadeOut( 400, function () { $( this ).remove(); } ); }, 4000 );
	}

	function t( key, fallback ) {
		return ( mm.i18n && mm.i18n[ key ] ) ? mm.i18n[ key ] : fallback;
	}

	function escHtml( str ) {
		return $( '<span>' ).text( String( str ) ).html();
	} 

}( jQuery, window.mm_ajax || {} ) );
