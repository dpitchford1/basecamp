/* ==========================================================================
   mm-post-media.js  —  Media Manager: Post/Page editor media frame extension
   Adds a "Media Folders" tab to the standard WordPress "Add Media" modal.
   Depends on: jQuery, wp.media (media-editor), jsTree
   ========================================================================== */

( function ( $, wp, mmAjax ) {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.media ) { return; }

	var ajaxUrl = mmAjax.url  || window.ajaxurl || '';
	var nonce   = mmAjax.nonce || '';

	/* -----------------------------------------------------------------------
	   Folder Browser State
	   A Backbone controller state that drives the Folders tab.
	----------------------------------------------------------------------- */

	var FolderBrowserState = wp.media.controller.State.extend( {
		defaults: {
			id:      'mm-folder-browser',
			title:   'Media Folders',
			content: 'mm-folder-browser',
			menu:    'default',
			toolbar: false,
			router:  false,
		},
	} );

	/* -----------------------------------------------------------------------
	   Folder Browser View
	   Two-pane layout: jsTree sidebar + paginated file grid.
	----------------------------------------------------------------------- */

	var FolderBrowserView = wp.media.View.extend( {
		className: 'mm-post-media-wrap',

		events: {
			'click .mm-pm-file-item':  'selectFile',
			'click #mm-pm-load-more':  'loadMore',
			'click #mm-pm-insert-btn': 'insertSelected',
		},

		initialize: function () {
			this.selectedId     = null;
			this.selectedFile   = null;  // full data object from the card
			this.activeFolderId = null;
			this.currentPage    = 1;
			this.totalCount     = 0;
			this.loadedCount    = 0;
		},

		render: function () {
			this.$el.html(
				'<div class="mm-pm-layout">' +
					'<div class="mm-pm-sidebar">' +
						'<div id="mm-pm-tree"></div>' +
					'</div>' +
					'<div class="mm-pm-content">' +
						'<div id="mm-pm-grid"><p class="mm-pm-placeholder">Select a folder to browse its files.</p></div>' +
						'<div class="mm-pm-footer">' +
							'<button type="button" id="mm-pm-load-more" class="button" style="display:none">Load More</button>' +
							'<button type="button" id="mm-pm-insert-btn" class="button button-primary" disabled>Select</button>' +
						'</div>' +
					'</div>' +
				'</div>'
			);

			// Init tree after the view is in the DOM.
			_.defer( _.bind( this.initTree, this ) );
			this.delegateEvents();
			return this;
		},

		/* -------------------------------------------------------------------
		   jsTree
		------------------------------------------------------------------- */

		initTree: function () {
			var self  = this;
			var $tree = this.$el.find( '#mm-pm-tree' );

			$.post( ajaxUrl, { action: 'mm_folder_tree', nonce: nonce }, function ( r ) {
				if ( ! r.success || ! r.data ) { return; }

				$tree.jstree( {
					core: {
						data:   r.data,
						themes: { icons: true, dots: true },
					},
					plugins: [ 'wholerow' ],
				} ).on( 'select_node.jstree', function ( _e, data ) {
					// Node IDs are prefixed "mm-{post_id}" — strip the prefix.
					var raw = String( data.node.id ).replace( /^mm-/, '' );
					var id  = parseInt( raw, 10 );
					if ( id ) { self.loadFolder( id ); }
				} );
			} );
		},

		/* -------------------------------------------------------------------
		   File loading
		------------------------------------------------------------------- */

		loadFolder: function ( folderId ) {
			this.activeFolderId = folderId;
			this.currentPage    = 1;
			this.totalCount     = 0;
			this.loadedCount    = 0;
			this.selectedId     = null;
			this.updateInsertButton();

			this.$el.find( '#mm-pm-grid' ).addClass( 'mm-loading' ).empty();
			this.$el.find( '#mm-pm-load-more' ).hide();

			this.fetchPage( folderId, 1, false );
		},

		loadMore: function () {
			if ( ! this.activeFolderId || this.loadedCount >= this.totalCount ) { return; }
			this.fetchPage( this.activeFolderId, this.currentPage + 1, true );
		},

		fetchPage: function ( folderId, page, append ) {
			var self  = this;
			var $grid = this.$el.find( '#mm-pm-grid' );
			var $more = this.$el.find( '#mm-pm-load-more' );

			$more.prop( 'disabled', true );

			$.post( ajaxUrl, {
				action:    'mm_folder_contents',
				nonce:     nonce,
				folder_id: folderId,
				page:      page,
			}, function ( r ) {
				$grid.removeClass( 'mm-loading' );

				if ( ! r.success ) {
					if ( ! append ) { $grid.html( '<p class="mm-pm-error">Could not load files.</p>' ); }
					$more.prop( 'disabled', false );
					return;
				}

				var d = r.data;
				self.currentPage  = page;
				self.totalCount   = d.total;
				self.loadedCount += d.files ? d.files.length : 0;

				self.renderFiles( d.files, append );

				if ( self.loadedCount < self.totalCount ) {
					$more.show().prop( 'disabled', false );
				} else {
					$more.hide();
				}
			} ).fail( function () {
				$grid.removeClass( 'mm-loading' );
				if ( ! append ) { $grid.html( '<p class="mm-pm-error">Could not load files.</p>' ); }
				$more.prop( 'disabled', false );
			} );
		},

		/* -------------------------------------------------------------------
		   Grid rendering
		------------------------------------------------------------------- */

		renderFiles: function ( files, append ) {
			var $grid = this.$el.find( '#mm-pm-grid' );

			if ( ! append ) { $grid.empty(); }

			if ( ! files || ! files.length ) {
				if ( ! append ) { $grid.html( '<p class="mm-pm-empty">No files in this folder.</p>' ); }
				return;
			}

			var html = '';
			for ( var i = 0; i < files.length; i++ ) {
				var f = files[ i ];
				var thumb = f.is_image && f.thumbnail
					? '<img src="' + _.escape( f.thumbnail ) + '" alt="' + _.escape( f.title || '' ) + '" loading="lazy">'
					: '<span class="dashicons dashicons-' + this.mimeIcon( f.mime ) + ' mm-pm-icon"></span>';

				html += '<div class="mm-pm-file-item"' +
					' data-id="'       + parseInt( f.id, 10 )          + '"' +
					' data-url="'      + _.escape( f.url )              + '"' +
					' data-is-image="' + ( f.is_image ? '1' : '0' )     + '"' +
					' data-title="'    + _.escape( f.title || '' )      + '"' +
					' data-filename="' + _.escape( f.filename || '' )   + '"' +
					' title="'         + _.escape( f.filename )         + '">' +
					'<div class="mm-pm-thumb">' + thumb + '</div>' +
					'<div class="mm-pm-name">' + _.escape( f.filename ) + '</div>' +
				'</div>';
			}

			if ( append ) {
				$grid.append( html );
			} else {
				$grid.html( html );
			}
		},

		mimeIcon: function ( mime ) {
			var map = {
				'application/pdf':   'media-document',
				'application/zip':   'media-archive',
				'audio/mpeg':        'media-audio',
				'audio/ogg':         'media-audio',
				'video/mp4':         'media-video',
				'video/quicktime':   'media-video',
				'text/plain':        'media-text',
			};
			return map[ mime ] || 'media-default';
		},

		/* -------------------------------------------------------------------
		   Selection
		------------------------------------------------------------------- */

		selectFile: function ( e ) {
			var $item = $( e.currentTarget );
			var id    = parseInt( $item.data( 'id' ), 10 );

			this.$el.find( '.mm-pm-file-item' ).removeClass( 'mm-pm-selected' );
			$item.addClass( 'mm-pm-selected' );
			this.selectedId   = id;
			this.selectedFile = {
				id:       id,
				url:      $item.data( 'url' ),
				isImage:  $item.data( 'is-image' ) === 1 || $item.data( 'is-image' ) === '1',
				title:    $item.data( 'title' ),
				filename: $item.data( 'filename' ),
			};
			this.updateInsertButton();
		},

		updateInsertButton: function () {
			this.$el.find( '#mm-pm-insert-btn' ).prop( 'disabled', ! this.selectedId );
		},

		insertSelected: function () {
			var self = this;
			var file = this.selectedFile;
			if ( ! file || ! file.id ) { return; }

			var attachment = wp.media.attachment( file.id );

			attachment.fetch().done( function () {
				// Find the state that was active before the user clicked our tab.
				// controller.lastState() returns the state model prior to 'mm-folder-browser'.
				// This may be 'insert' (Add Media), 'featured-image', or any other frame state.
				var prevState = self.controller.lastState
					? self.controller.lastState()
					: null;

				if ( prevState && prevState.get( 'selection' ) ) {
					// Reset the previous state's selection to our chosen attachment, then
					// switch back — WP's own context-aware toolbar button ('Insert into post',
					// 'Set featured image', etc.) handles the final action.
					prevState.get( 'selection' ).reset( [ attachment ] );
					self.controller.setState( prevState.id );
				} else {
					// Fallback for frames without a selectable state (should be rare).
					var html = file.isImage
						? '<img src="' + _.escape( file.url ) + '" alt="' + file.title.replace( /"/g, '&quot;' ) + '">'
						: '<a href="' + _.escape( file.url ) + '">' + _.escape( file.title || file.filename ) + '</a>';
					wp.media.editor.insert( html );
					self.controller.close();
				}
			} ).fail( function () {
				// Attachment fetch failed — nothing to do.
				window.console && console.warn( 'Media Manager: could not load attachment ' + file.id );
			} );
		},
	} );

	/* -----------------------------------------------------------------------
	   Extend wp.media.view.MediaFrame.Post
	   Covers: "Add Media" and any wp.media() call that uses the Post frame.
	   Because this prototype swap happens at script-load time, every modal
	   opened after this point automatically gets the "Media Folders" tab —
	   no per-button wiring needed.
	----------------------------------------------------------------------- */

	var OriginalPost = wp.media.view.MediaFrame.Post;

	wp.media.view.MediaFrame.Post = OriginalPost.extend( {

		initialize: function () {
			OriginalPost.prototype.initialize.apply( this, arguments );
			this.states.add( [ new FolderBrowserState() ] );
		},

		bindHandlers: function () {
			OriginalPost.prototype.bindHandlers.apply( this, arguments );
			this.on( 'content:create:mm-folder-browser', this.mmCreateFolderContent, this );
		},

		browseRouter: function ( routerView ) {
			OriginalPost.prototype.browseRouter.apply( this, arguments );
			routerView.set( {
				'mm-folder-browser': {
					text:     'Media Folders',
					priority: 60,
				},
			} );
		},

		mmCreateFolderContent: function ( contentRegion ) {
			this._mmFolderView = new FolderBrowserView( { controller: this } );
			contentRegion.view = this._mmFolderView;
		},
	} );

	/* -----------------------------------------------------------------------
	   Extend wp.media.view.MediaFrame.Select
	   Covers: Featured Image and any other single-file picker that WP creates
	   as a Select frame (NOT a Post frame). These frames already show a tab
	   router ("Upload Files" / "Media Library") — we chain browseRouter to
	   add our tab alongside those.

	   MediaFrame.Post extends MediaFrame.Select, but the two are extended
	   independently here so there is no double-application: Post frames call
	   our Post extension; Select frames (featured image) call this one.
	----------------------------------------------------------------------- */

	if ( wp.media.view.MediaFrame.Select ) {
		var OriginalSelect = wp.media.view.MediaFrame.Select;

		wp.media.view.MediaFrame.Select = OriginalSelect.extend( {

			initialize: function () {
				OriginalSelect.prototype.initialize.apply( this, arguments );
				// Guard: Post frames are extended separately above and must
				// not get a second copy of the state.
				if ( ! this.states.get( 'mm-folder-browser' ) ) {
					this.states.add( [ new FolderBrowserState() ] );
				}
			},

			bindHandlers: function () {
				OriginalSelect.prototype.bindHandlers.apply( this, arguments );
				this.on( 'content:create:mm-folder-browser', this.mmCreateFolderContent, this );
			},

			// Chain browseRouter — this is what adds "Upload Files" / "Media Library"
			// tabs on Select frames. We append our own tab after those.
			browseRouter: function ( routerView ) {
				OriginalSelect.prototype.browseRouter.apply( this, arguments );
				routerView.set( {
					'mm-folder-browser': {
						text:     'Media Folders',
						priority: 60,
					},
				} );
			},

			mmCreateFolderContent: function ( contentRegion ) {
				this._mmFolderView = new FolderBrowserView( { controller: this } );
				contentRegion.view = this._mmFolderView;
			},
		} );
	}

} )( jQuery, wp, window.mm_ajax || {} );
