/* ==========================================================================
   mm-library-files.js  —  Media Manager: file selection, bulk actions,
                            rename, drag-drop move, sync, upload, attachment edit
   Depends on: mm-library.js (window.mmLib must exist)
   ========================================================================== */

( function ( $, lib ) {
	'use strict';

	var s  = lib.state;
	var mm = lib.mm; 

	/**
	 * Shorthand i18n lookup delegating to lib.t.
	 *
	 * @param  {string} key      i18n key.
	 * @param  {string} fallback Default text.
	 * @return {string}
	 */
	function t( key, fallback ) { return lib.t( key, fallback ); }

	/* -----------------------------------------------------------------------
	   Checkbox handling
	----------------------------------------------------------------------- */

	/**
	 * Handle a checkbox change event on a file grid item.
	 * Supports shift-click range selection.
	 *
	 * @param  {jQuery.Event} e  The change event.
	 * @return {void}
	 */
	lib.handleCheckboxChange = function ( e ) {
		var $cb   = $( e.target );
		var index = parseInt( $cb.data( 'index' ), 10 );

		if ( e.shiftKey && s.lastCheckedIdx >= 0 ) {
			var min   = Math.min( s.lastCheckedIdx, index );
			var max   = Math.max( s.lastCheckedIdx, index );
			var state = $cb.prop( 'checked' );
			s.$fileGrid.find( '.mm-file-checkbox' ).each( function () {
				var i = parseInt( $( this ).data( 'index' ), 10 );
				if ( i >= min && i <= max ) {
					$( this ).prop( 'checked', state );
					$( this ).closest( '.mm-file-item' ).toggleClass( 'mm-selected', state );
				}
			} );
		} else {
			$cb.closest( '.mm-file-item' ).toggleClass( 'mm-selected', $cb.prop( 'checked' ) );
		}

		s.lastCheckedIdx = index;
		lib.updateSelectAll();
		lib.updateBulkToolbar();
	};

	/**
	 * Handle a click on a file grid item (excluding checkbox, link, and icon targets).
	 * Toggles the item's checkbox.
	 *
	 * @param  {jQuery.Event} e  The click event.
	 * @return {void}
	 */
	lib.handleItemClick = function ( e ) {
		if ( $( e.target ).is( 'input, a, img, .dashicons' ) ) { return; }
		var $cb = $( e.currentTarget ).find( '.mm-file-checkbox' );
		$cb.prop( 'checked', ! $cb.prop( 'checked' ) ).trigger( 'change' );
	};

	/**
	 * Bind the #mm-select-all master checkbox to toggle all file checkboxes.
	 *
	 * @return {void}
	 */
	lib.initSelectAll = function () {
		$( document ).on( 'change', '#mm-select-all', function () {
			var checked = $( this ).prop( 'checked' );
			s.$fileGrid.find( '.mm-file-checkbox' ).prop( 'checked', checked );
			s.$fileGrid.find( '.mm-file-item' ).toggleClass( 'mm-selected', checked );
			s.lastCheckedIdx = -1;
			lib.updateBulkToolbar();
		} );
	};

	/**
	 * Sync the #mm-select-all checkbox state (checked / indeterminate / unchecked)
	 * to reflect the current file checkbox selection.
	 *
	 * @return {void}
	 */
	lib.updateSelectAll = function () {
		if ( ! s.$selectAll.length ) { return; }
		var $all     = s.$fileGrid.find( '.mm-file-checkbox' );
		var $checked = $all.filter( ':checked' );
		s.$selectAll.prop( 'indeterminate', $checked.length > 0 && $checked.length < $all.length );
		s.$selectAll.prop( 'checked', $all.length > 0 && $checked.length === $all.length );
	};

	/**
	 * Return the attachment IDs of all currently checked file grid items.
	 *
	 * @return {number[]}
	 */
	function getSelectedIds() {
		return s.$fileGrid.find( '.mm-file-checkbox:checked' ).map( function () {
			return parseInt( $( this ).val(), 10 );
		} ).get();
	}

	/* -----------------------------------------------------------------------
	   Bulk toolbar
	----------------------------------------------------------------------- */

	/**
	 * Show or hide bulk action controls based on the current selection count.
	 *
	 * @return {void}
	 */
	lib.updateBulkToolbar = function () {
		var count = getSelectedIds().length;
		$( '#mm-selection-count' ).text( count ? count + ' ' + t( 'selected', 'selected' ) : '' );
		$( '#mm-bulk-actions' ).toggleClass( 'mm-bulk-active', count > 0 );
		$( '#mm-bulk-rename' ).toggle( count === 1 );
		$( '#mm-bulk-move-wrap' ).toggle( count > 0 );
		if ( count !== 1 ) { $( '#mm-rename-form' ).hide(); }
	};

	/* -----------------------------------------------------------------------
	   Bulk actions
	----------------------------------------------------------------------- */

	/**
	 * Bind bulk delete and bulk move action buttons.
	 *
	 * @return {void}
	 */
	lib.initBulkActions = function () {
		$( document ).on( 'click', '#mm-bulk-delete', function () {
			var ids = getSelectedIds();
			if ( ! ids.length ) { return; }
			if ( ! window.confirm( t( 'confirm_delete_files', 'Delete ' + ids.length + ' file(s)? This cannot be undone.' ) ) ) { return; }
			$.post( mm.url, {
				action:         'mm_delete_files',
				nonce:          mm.nonce,
				attachment_ids: ids,
			}, function ( r ) {
				if ( r.success ) { lib.loadFolder( s.activeFolderId ); }
				else { window.alert( ( r.data && r.data.message ) || t( 'error_delete', 'Delete failed.' ) ); }
			} );
		} );

		$( document ).on( 'click', '#mm-bulk-move-apply', function () {
			var destId = parseInt( $( '#mm-bulk-move-folder' ).val(), 10 );
			if ( ! destId ) { return; }
			var ids = getSelectedIds();
			if ( ! ids.length ) { return; }
			doMove( ids, destId );
		} );
	};

	/* -----------------------------------------------------------------------
	   Move (sequential, with collision feedback)
	----------------------------------------------------------------------- */

	/**
	 * Move an array of attachment IDs to the given destination folder, one at a time.
	 * Shows an alert if any moves fail; shows a grid notice if any files were renamed
	 * to resolve a filename collision.
	 *
	 * @param  {number[]} ids           Attachment IDs to move.
	 * @param  {number}   destFolderId  ID of the destination mm_folder post.
	 * @return {void}
	 */
	function doMove( ids, destFolderId ) {
		var processed = 0, errors = [], renamed = [];

		function next() {
			if ( processed >= ids.length ) {
				if ( errors.length ) {
					window.alert( t( 'error_move', 'Some files could not be moved.' ) + '\n' + errors.join( '\n' ) );
				}
				if ( s.activeFolderId ) {
					lib.loadFolder( s.activeFolderId );
					if ( renamed.length ) {
						setTimeout( function () {
							lib.showGridNotice( t( 'move_renamed', 'Renamed to avoid a conflict:' ) + ' ' + renamed.join( ', ' ), 'warning' );
						}, 400 );
					}
				}
				return;
			}

			var attachmentId = ids[ processed++ ];
			$.post( mm.url, {
				action:         'mm_move_copy_file',
				nonce:          mm.nonce,
				attachment_id:  attachmentId,
				dest_folder_id: destFolderId,
				mode:           'move',
			}, function ( r ) {
				if ( ! r.success ) {
					errors.push( ( r.data && r.data.message ) || 'ID ' + attachmentId );
				} else if ( r.data && r.data.filename_changed ) {
					renamed.push( r.data.new_filename );
				}
				next();
			} ).fail( function () {
				errors.push( 'ID ' + attachmentId + ' (network error)' );
				next();
			} );
		}

		next();
	}

	/* -----------------------------------------------------------------------
	   Bulk Move — folder select helpers
	----------------------------------------------------------------------- */

	/**
	 * Walk the live jsTree and populate the bulk-move folder <select> with a
	 * depth-indented flat list of all real folders.
	 *
	 * @return {void}
	 */
	lib.flattenTreeForSelect = function () {
		var inst = s.$tree.jstree( true );
		if ( ! inst ) { return; }
		var options = [];
		flattenNode( inst, '#', -1, options );
		populateMoveSelect( options );
	};

	/**
	 * Recursively traverse a jsTree node and append real folders to the output array.
	 *
	 * @param  {object}   inst    jsTree instance.
	 * @param  {string}   nodeId  jsTree node ID to start from ('#' for root).
	 * @param  {number}   depth   Current depth level (used for indentation in the select).
	 * @param  {Array<{id: number, name: string, depth: number}>} out  Accumulator array.
	 * @return {void}
	 */
	function flattenNode( inst, nodeId, depth, out ) {
		var node = inst.get_node( nodeId );
		if ( ! node ) { return; }
		if ( nodeId !== '#' ) {
			var fid = node.data && node.data.folder_id ? node.data.folder_id : 0;
			// Skip virtual nodes (folder_id < 0) — cannot move files there.
			if ( fid > 0 ) { out.push( { id: fid, name: node.text, depth: depth } ); }
		}
		if ( node.children && node.children.length ) {
			for ( var i = 0; i < node.children.length; i++ ) {
				flattenNode( inst, node.children[ i ], depth + 1, out );
			}
		}
	}

	/**
	 * Populate the #mm-bulk-move-folder <select> with depth-indented folder options.
	 *
	 * @param  {Array<{id: number, name: string, depth: number}>} options  Flat folder list.
	 * @return {void}
	 */
	function populateMoveSelect( options ) {
		var $sel = $( '#mm-bulk-move-folder' );
		if ( ! $sel.length ) { return; }
		$sel.empty().append( '<option value="">' + t( 'move_to_folder', '\u2014 Move to folder \u2014' ) + '</option>' );
		for ( var i = 0; i < options.length; i++ ) {
			var f      = options[ i ];
			var indent = new Array( Math.max( 0, f.depth ) + 1 ).join( '\u00a0\u00a0\u00a0' );
			$sel.append( $( '<option>' ).val( f.id ).text( indent + f.name ) );
		}
	}

	/* -----------------------------------------------------------------------
	   Rename
	----------------------------------------------------------------------- */

	/**
	 * Bind rename toolbar button, cancel button, and submit (click + Enter key) handlers.
	 *
	 * @return {void}
	 */
	lib.initRename = function () {
		$( document ).on( 'click', '#mm-bulk-rename', function () {
			var ids = getSelectedIds();
			if ( ids.length !== 1 ) { return; }

			var $item    = s.$fileGrid.find( '.mm-file-item[data-id="' + ids[ 0 ] + '"]' );
			var $name    = $item.find( '.mm-file-name' );
			var filename = $name.attr( 'title' ) || $name.text();
			var ext      = filename.indexOf( '.' ) !== -1 ? filename.split( '.' ).pop() : '';
			var base     = ext ? filename.slice( 0, -( ext.length + 1 ) ) : filename;

			$( '#mm-rename-base' ).val( base );
			$( '#mm-rename-ext' ).text( ext ? '.' + ext : '' );
			$( '#mm-rename-form' ).data( 'attachment-id', ids[ 0 ] ).show();
			$( '#mm-rename-base' ).trigger( 'focus' ).select();
		} );

		$( document ).on( 'click', '#mm-rename-cancel', function () {
			$( '#mm-rename-form' ).hide();
		} );

		$( document ).on( 'click keydown', '#mm-rename-submit, #mm-rename-base', function ( e ) {
			if ( e.type === 'keydown' && e.which !== 13 ) { return; }
			if ( $( this ).is( '#mm-rename-base' ) && e.type !== 'keydown' ) { return; }

			var $form   = $( '#mm-rename-form' );
			var id      = $form.data( 'attachment-id' );
			var newName = $.trim( $( '#mm-rename-base' ).val() );
			if ( ! newName ) { return; }

			$( '#mm-rename-submit' ).prop( 'disabled', true );

			$.post( mm.url, {
				action:        'mm_rename_file',
				nonce:         mm.nonce,
				attachment_id: id,
				new_name:      newName,
				update_title:  $( '#mm-rename-update-title' ).is( ':checked' ) ? '1' : '0',
			}, function ( r ) {
				$( '#mm-rename-submit' ).prop( 'disabled', false );
				$form.hide();

				if ( ! r.success ) {
					window.alert( ( r.data && r.data.message ) || t( 'error_rename', 'Rename failed.' ) );
					return;
				}

				var fd    = r.data;
				var $item = s.$fileGrid.find( '.mm-file-item[data-id="' + id + '"]' );
				$item.find( '.mm-file-name' ).text( fd.filename ).attr( 'title', fd.filename );
				if ( fd.thumbnail ) { $item.find( 'img' ).attr( 'src', fd.thumbnail ); }
			} );
		} );
	};

	/* -----------------------------------------------------------------------
	   Drag-and-drop move
	----------------------------------------------------------------------- */

	/**
	 * Enable drag-and-drop move from the file grid to jsTree folder nodes.
	 * Sets draggedIds on dragstart; clears them on dragend.
	 *
	 * @return {void}
	 */
	lib.initDragDrop = function () {
		s.$fileGrid.on( 'dragstart', '.mm-file-item', function ( e ) {
			var id      = parseInt( $( this ).data( 'id' ), 10 );
			var checked = getSelectedIds();
			s.draggedIds = ( checked.indexOf( id ) !== -1 ) ? checked : [ id ];
			e.originalEvent.dataTransfer.effectAllowed = 'move';
			e.originalEvent.dataTransfer.setData( 'text/plain', s.draggedIds.join( ',' ) );
		} );

		s.$fileGrid.on( 'dragend', '.mm-file-item', function () {
			$( '#mm-folder-tree .mm-drop-target' ).removeClass( 'mm-drop-target' );
			s.draggedIds = [];
		} );

		s.$tree.on( 'ready.jstree refresh.jstree', bindTreeDropTargets );
	};

	/**
	 * Attach native dragover/dragleave/drop listeners to each jsTree folder node.
	 * Called after tree ready and refresh events to rebind newly rendered nodes.
	 *
	 * @return {void}
	 */
	function bindTreeDropTargets() {
		$( '#mm-folder-tree li.jstree-node' ).each( function () {
			var li = this;

			li.addEventListener( 'dragover', function ( e ) {
				if ( ! s.draggedIds.length ) { return; }
				e.preventDefault();
				e.stopPropagation();
				e.dataTransfer.dropEffect = 'move';
				$( '#mm-folder-tree .mm-drop-target' ).removeClass( 'mm-drop-target' );
				$( li ).addClass( 'mm-drop-target' );
			}, false );

			li.addEventListener( 'dragleave', function ( e ) {
				if ( li.contains( e.relatedTarget ) ) { return; }
				$( li ).removeClass( 'mm-drop-target' );
			}, false );

			li.addEventListener( 'drop', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
				$( li ).removeClass( 'mm-drop-target' );
				if ( ! s.draggedIds.length ) { return; }

				var inst   = s.$tree.jstree( true );
				var node   = inst.get_node( li );
				var destId = node && node.data && node.data.folder_id ? node.data.folder_id : 0;

				// Block drops on virtual nodes (Unassigned) or the current folder.
				if ( ! destId || destId < 0 || destId === s.activeFolderId ) {
					s.draggedIds = [];
					return;
				}

				var ids = s.draggedIds.slice();
				s.draggedIds = [];
				doMove( ids, destId );
			}, false );
		} );
	}

	/* -----------------------------------------------------------------------
	   Sync folder
	----------------------------------------------------------------------- */

	/**
	 * Bind the Sync Folder button. Scans the active folder for untracked files
	 * and imports them in chunks.
	 *
	 * @return {void}
	 */
	lib.initSync = function () {
		$( document ).on( 'mm:folder-selected', function () {
			$( '#mm-sync-btn' ).prop( 'disabled', false );
		} );

		$( document ).on( 'click', '#mm-sync-btn', function () {
			if ( ! s.activeFolderId || s.activeFolderId < 0 ) { return; }

			var $btn = $( this );
			$btn.prop( 'disabled', true );
			$btn.find( '.mm-sync-icon' ).addClass( 'mm-spin' );

			$.post( mm.url, {
				action:    'mm_sync_folder',
				nonce:     mm.nonce,
				folder_id: s.activeFolderId,
			}, function ( r ) {
				if ( ! r.success ) {
					$btn.prop( 'disabled', false );
					$btn.find( '.mm-sync-icon' ).removeClass( 'mm-spin' );
					window.alert( ( r.data && r.data.message ) || t( 'sync_error', 'Sync failed.' ) );
					return;
				}

				if ( r.data.total === 0 ) {
					$btn.prop( 'disabled', false );
					$btn.find( '.mm-sync-icon' ).removeClass( 'mm-spin' );
					return;
				}

				var chunk = 0;
				function nextChunk() {
					$.post( mm.url, {
						action:      'mm_sync_chunk',
						nonce:       mm.nonce,
						folder_id:   s.activeFolderId,
						chunk_index: chunk,
					}, function ( cr ) {
						chunk++;
						if ( cr.success && cr.data.remaining > 0 ) {
							nextChunk();
						} else {
							$btn.prop( 'disabled', false );
							$btn.find( '.mm-sync-icon' ).removeClass( 'mm-spin' );
							lib.loadFolder( s.activeFolderId );
						}
					} ).fail( function () {
						$btn.prop( 'disabled', false );
						$btn.find( '.mm-sync-icon' ).removeClass( 'mm-spin' );
					} );
				}
				nextChunk();
			} );
		} );
	};

	/* -----------------------------------------------------------------------
	   Upload listener
	----------------------------------------------------------------------- */

	/**
	 * Listen for the custom mm:file-uploaded event (fired by the upload zone)
	 * and prepend the new file to the grid without a full reload.
	 *
	 * @return {void}
	 */
	lib.initUploadListener = function () {
		$( document ).on( 'mm:file-uploaded', function ( _e, fileData ) {
			if ( ! fileData || ! fileData.id ) { return; }
			s.$fileGrid.find( '.mm-empty' ).remove();
			var idx = s.$fileGrid.find( '.mm-file-item' ).length;
			s.$fileGrid.prepend( lib.renderFileItem( fileData, idx ) );
			var current = parseInt( s.$folderCount.text(), 10 ) || 0;
			s.$folderCount.text( ( current + 1 ) + ' ' + t( 'files', 'files' ) );
		} );
	};

	/* -----------------------------------------------------------------------
	   WP native attachment edit modal
	----------------------------------------------------------------------- */

	/**
	 * Intercept clicks on image thumbnails and open the WP native attachment
	 * edit modal (wp.media frame) instead of navigating to the edit URL.
	 * Reloads the active folder on modal close to reflect any title changes.
	 *
	 * @return {void}
	 */
	lib.initAttachmentEdit = function () {
		if ( typeof wp === 'undefined' || ! wp.media ) { return; }

		s.$fileGrid.on( 'click', '.mm-file-thumb', function ( e ) {
			var $item   = $( this ).closest( '.mm-file-item' );
			var isImage = $item.data( 'is-image' ) === 1 || $item.data( 'is-image' ) === '1';
			if ( ! isImage ) { return; }

			e.preventDefault();
			e.stopPropagation();

			var attachmentId = parseInt( $item.data( 'id' ), 10 );
			if ( ! attachmentId ) { return; }

			var attachment = wp.media.attachment( attachmentId );
			attachment.fetch();

			var frame = wp.media( {
				title:    '',
				frame:    'select',
				multiple: false,
				library:  { post__in: [ attachmentId ] },
			} );

			frame.on( 'open', function () {
				var state = frame.state();
				if ( state && state.get ) {
					state.get( 'selection' ).reset( [ attachment ] );
				}
			} );

			frame.on( 'close', function () {
				if ( s.activeFolderId > 0 ) { lib.loadFolder( s.activeFolderId ); }
			} );

			frame.open();
		} );
	};

}( jQuery, window.mmLib ) );
