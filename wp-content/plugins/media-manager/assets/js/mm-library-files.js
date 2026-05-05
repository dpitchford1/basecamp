/* ==========================================================================
   mm-library-files.js  —  Media Manager: file selection, bulk actions,
                            rename, drag-drop move, sync, upload, attachment edit
   Depends on: mm-library.js (window.mmLib must exist)
   ========================================================================== */

( function ( $, lib ) {
	'use strict';

	var s  = lib.state;
	var mm = lib.mm;

	function t( key, fallback ) { return lib.t( key, fallback ); }

	/* -----------------------------------------------------------------------
	   Checkbox handling
	----------------------------------------------------------------------- */

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

	lib.handleItemClick = function ( e ) {
		if ( $( e.target ).is( 'input, a, img, .dashicons' ) ) { return; }
		var $cb = $( e.currentTarget ).find( '.mm-file-checkbox' );
		$cb.prop( 'checked', ! $cb.prop( 'checked' ) ).trigger( 'change' );
	};

	lib.initSelectAll = function () {
		$( document ).on( 'change', '#mm-select-all', function () {
			var checked = $( this ).prop( 'checked' );
			s.$fileGrid.find( '.mm-file-checkbox' ).prop( 'checked', checked );
			s.$fileGrid.find( '.mm-file-item' ).toggleClass( 'mm-selected', checked );
			s.lastCheckedIdx = -1;
			lib.updateBulkToolbar();
		} );
	};

	lib.updateSelectAll = function () {
		if ( ! s.$selectAll.length ) { return; }
		var $all     = s.$fileGrid.find( '.mm-file-checkbox' );
		var $checked = $all.filter( ':checked' );
		s.$selectAll.prop( 'indeterminate', $checked.length > 0 && $checked.length < $all.length );
		s.$selectAll.prop( 'checked', $all.length > 0 && $checked.length === $all.length );
	};

	function getSelectedIds() {
		return s.$fileGrid.find( '.mm-file-checkbox:checked' ).map( function () {
			return parseInt( $( this ).val(), 10 );
		} ).get();
	}

	/* -----------------------------------------------------------------------
	   Bulk toolbar
	----------------------------------------------------------------------- */

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

	lib.flattenTreeForSelect = function () {
		var inst = s.$tree.jstree( true );
		if ( ! inst ) { return; }
		var options = [];
		flattenNode( inst, '#', -1, options );
		populateMoveSelect( options );
	};

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
