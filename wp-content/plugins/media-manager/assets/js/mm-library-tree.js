/* ==========================================================================
   mm-library-tree.js  —  Media Manager: jsTree, context menu, folder CRUD,
                           hover preview strip, recent-uploads filter
   Depends on: mm-library.js (window.mmLib must exist)
   ========================================================================== */

( function ( $, lib ) {
	'use strict';

	var s  = lib.state;
	var mm = lib.mm;

	function t( key, fallback ) { return lib.t( key, fallback ); }

	/* -----------------------------------------------------------------------
	   Tree init
	----------------------------------------------------------------------- */

	lib.initTree = function () {
		$.post( mm.url, { action: 'mm_folder_tree', nonce: mm.nonce }, function ( r ) {
			if ( ! r.success ) { showTreeError( r.data && r.data.message ); return; }
			buildTree( r.data );
		} ).fail( showTreeError );
	};

	function buildTree( nodes ) {
		s.$tree.jstree( {
			core: {
				data:           nodes,
				multiple:       false,
				check_callback: true,
				themes: { responsive: false, dots: true, icons: true },
			},
			types: {
				'#':    { valid_children: [ 'folder' ] },
				folder: { icon: 'dashicons dashicons-category' },
			},
			plugins: [ 'contextmenu', 'wholerow', 'types' ],
			contextmenu: { select_node: false, items: buildContextMenu },
		} );

		s.$tree.on( 'select_node.jstree', function ( _e, data ) {
			var id = data.node.data && data.node.data.folder_id ? data.node.data.folder_id : 0;
			if ( id ) {
				s.filterMode = null;
				$( '#mm-recent-clear' ).hide();
				$( '.mm-recent-btn' ).removeClass( 'mm-filter-active' );
				lib.loadFolder( id );
			}
		} );

		s.$tree.on( 'ready.jstree', function () {
			var rootId = s.$tree.jstree( 'get_node', '#' ).children[ 0 ];
			if ( rootId ) { s.$tree.jstree( 'select_node', rootId ); }
			loadProtectedFolderIds();
			lib.flattenTreeForSelect();

			// Virtual "Unassigned" node — shows attachments with no folder row.
			s.$tree.jstree( 'create_node', '#', {
				id:   'mm-unassigned',
				text: t( 'unassigned', 'Unassigned' ),
				icon: 'dashicons dashicons-warning',
				data: { folder_id: -1 },
			}, 'last' );
		} );

		s.$tree.on( 'refresh.jstree', lib.flattenTreeForSelect );
	}

	lib.reloadTree = function () {
		$.post( mm.url, { action: 'mm_refresh_folders', nonce: mm.nonce }, function ( r ) {
			if ( r.success ) {
				var inst = s.$tree.jstree( true );
				inst.settings.core.data = r.data.tree;
				inst.refresh();
			}
		} );
	};

	function loadProtectedFolderIds() {
		$.post( mm.url, { action: 'mm_get_protected_files', nonce: mm.nonce }, function ( r ) {
			s.protectedFolderIds = {};
			if ( r.success && r.data.folders ) {
				$.each( r.data.folders, function ( _, row ) {
					s.protectedFolderIds[ row.folder_id ] = true;
				} );
			}
		} );
	}

	function showTreeError( message ) {
		message = message || t( 'tree_error', 'Could not load folder tree.' );
		s.$tree.html( '<p class="mm-error">' + lib.escHtml( message ) + '</p>' );
	}

	/* -----------------------------------------------------------------------
	   Context menu
	----------------------------------------------------------------------- */

	function buildContextMenu( node ) {
		var folderId    = node.data && node.data.folder_id ? node.data.folder_id : 0;
		var isProtected = folderId > 0 && s.protectedFolderIds[ folderId ];

		// Virtual nodes (folder_id < 0) have no folder operations.
		if ( folderId < 0 ) { return {}; }

		return {
			new_folder: {
				label:  t( 'new_folder', 'New folder' ),
				icon:   'dashicons dashicons-plus-alt2',
				action: function () { promptCreateFolder( folderId ); },
			},
			protect_folder: {
				label:  isProtected ? t( 'unprotect_folder', 'Remove protection' ) : t( 'protect_folder', 'Protect folder' ),
				icon:   isProtected ? 'dashicons dashicons-unlock' : 'dashicons dashicons-lock',
				action: function () { toggleFolderProtection( folderId, isProtected ); },
				separator_before: true,
			},
			hide_folder: {
				label:  t( 'hide_folder', 'Hide folder' ),
				icon:   'dashicons dashicons-hidden',
				action: function () { toggleHideFolder( folderId ); },
			},
			delete_folder: {
				label:  t( 'delete_folder', 'Delete folder' ),
				icon:   'dashicons dashicons-trash',
				action: function () { confirmDeleteFolder( folderId ); },
			},
		};
	}

	function promptCreateFolder( parentId ) {
		var name = window.prompt( t( 'new_folder_name', 'New folder name:' ) );
		if ( ! name ) { return; }
		$.post( mm.url, {
			action: 'mm_create_folder', nonce: mm.nonce,
			name: name, parent_id: parentId,
		}, function ( r ) {
			if ( ! r.success ) {
				window.alert( ( r.data && r.data.message ) || t( 'error_create', 'Could not create folder.' ) );
				return;
			}
			s.$tree.jstree( 'create_node', parentId ? 'mm-' + parentId : '#', r.data.node );
		} );
	}

	function toggleHideFolder( folderId ) {
		$.post( mm.url, {
			action: 'mm_hide_folder', nonce: mm.nonce,
			folder_id: folderId, hidden: 1,
		}, function ( r ) {
			if ( r.success && r.data.hidden ) {
				s.$tree.jstree( 'delete_node', 'mm-' + folderId );
			}
		} );
	}

	function confirmDeleteFolder( folderId ) {
		if ( ! window.confirm( t( 'confirm_delete_folder', 'Delete this folder? It must be empty.' ) ) ) { return; }
		$.post( mm.url, {
			action: 'mm_delete_folder', nonce: mm.nonce, folder_id: folderId,
		}, function ( r ) {
			if ( ! r.success ) {
				window.alert( ( r.data && r.data.message ) || t( 'error_delete', 'Could not delete folder.' ) );
				return;
			}
			s.$tree.jstree( 'delete_node', 'mm-' + folderId );
			if ( s.activeFolderId === folderId ) {
				s.activeFolderId = 0;
				s.$fileGrid.empty();
				s.$folderHeading.text( '' );
				s.$folderCount.text( '' );
			}
		} );
	}

	function toggleFolderProtection( folderId, currentlyProtected ) {
		$.post( mm.url, {
			action:    'mm_toggle_file_access',
			nonce:     mm.nonce,
			folder_id: folderId,
			protect:   currentlyProtected ? 0 : 1,
		}, function ( r ) {
			if ( ! r.success ) {
				window.alert( ( r.data && r.data.message ) || t( 'error_protect', 'Could not update folder protection.' ) );
				return;
			}
			if ( r.data.protected ) {
				s.protectedFolderIds[ folderId ] = true;
			} else {
				delete s.protectedFolderIds[ folderId ];
			}
		} );
	}

	lib.initFolderActions = function () {
		$( document ).on( 'click', '#mm-refresh-tree', lib.reloadTree );
	};

	/* -----------------------------------------------------------------------
	   Folder thumbnail hover preview (Todo 7)
	----------------------------------------------------------------------- */

	lib.initFolderPreview = function () {
		var $preview   = $( '#mm-folder-preview' );
		var hoverTimer = null;
		var lastNodeId = null;

		s.$tree.on( 'mouseenter', '.jstree-node', function () {
			var nodeId = $( this ).attr( 'id' );
			if ( nodeId === lastNodeId ) { return; }
			lastNodeId = nodeId;
			clearTimeout( hoverTimer );

			var node = s.$tree.jstree( 'get_node', nodeId );
			if ( ! node || ! node.data || ! node.data.folder_id || node.data.folder_id < 1 ) {
				$preview.hide();
				return;
			}

			var folderId = node.data.folder_id;
			hoverTimer = setTimeout( function () {
				$.post( mm.url, { action: 'mm_folder_thumbs', nonce: mm.nonce, folder_id: folderId }, function ( r ) {
					if ( ! r.success ) { return; }
					var thumbs = r.data.thumbs || [];
					if ( ! thumbs.length ) {
						$preview.html( '<span class="mm-preview-empty">' + lib.escHtml( t( 'no_images', 'No images' ) ) + '</span>' ).show();
					} else {
						var html = '';
						for ( var i = 0; i < thumbs.length; i++ ) {
							html += '<img src="' + lib.escAttr( thumbs[ i ] ) + '" alt="" loading="lazy">';
						}
						$preview.html( html ).show();
					}
					// Position preview to the right of the hovered node.
					var $anchor = s.$tree.find( '#' + nodeId + ' > .jstree-wholerow, #' + nodeId + ' > a' ).first();
					if ( $anchor.length ) {
						var off     = $anchor.offset();
						var treeOff = s.$tree.offset();
						$preview.css( {
							top:  ( off.top - treeOff.top + s.$tree.scrollTop() ) + 'px',
							left: ( s.$tree.outerWidth() + 4 ) + 'px',
						} );
					}
				} );
			}, 350 );
		} );

		s.$tree.on( 'mouseleave', function () {
			clearTimeout( hoverTimer );
			lastNodeId = null;
			$preview.hide();
		} );
	};

	/* -----------------------------------------------------------------------
	   Recent uploads filter (Todo 8)
	----------------------------------------------------------------------- */

	lib.initRecentFilter = function () {
		$( document ).on( 'click', '.mm-recent-btn', function () {
			var days = parseInt( $( this ).data( 'days' ), 10 );
			s.filterMode     = 'recent';
			s.filterDays     = days;
			s.activeFolderId = 0;
			s.currentPage    = 1;
			s.lastCheckedIdx = -1;
			s.$fileGrid.addClass( 'mm-loading' ).empty();
			s.$folderCount.text( '' );
			$( '#mm-pagination' ).empty();
			$( '.mm-recent-btn' ).removeClass( 'mm-filter-active' );
			$( this ).addClass( 'mm-filter-active' );
			$( '#mm-recent-clear' ).show();
			s.$tree.jstree( 'deselect_all' );
			lib.fetchFilterPage( days, 1 );
		} );

		$( document ).on( 'click', '#mm-recent-clear', function () {
			s.filterMode = null;
			$( this ).hide();
			$( '.mm-recent-btn' ).removeClass( 'mm-filter-active' );
			s.$fileGrid.empty().html(
				'<p class="mm-placeholder">' + lib.escHtml( t( 'select_folder', 'Select a folder to view its contents.' ) ) + '</p>'
			);
			s.$folderHeading.text( '' );
			s.$folderCount.text( '' );
			$( '#mm-pagination' ).empty();
		} );
	};

}( jQuery, window.mmLib ) );
