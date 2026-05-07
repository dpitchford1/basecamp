/* ==========================================================================
   mm-library-grid.js  —  Media Manager: folder loading, grid render,
                           pagination, sort controls, grid notices
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
	   Load folder (entry point — dispatches to the right fetch function)
	----------------------------------------------------------------------- */

	/**
	 * Entry point for loading a folder into the file grid.
	 * Resets pagination, clears selections, and dispatches to the correct fetch function.
	 *
	 * @param  {number} folderId  mm_folder post ID, or -1 for the virtual Unassigned node.
	 * @return {void}
	 */
	lib.loadFolder = function ( folderId ) {
		s.activeFolderId = folderId;
		s.filterMode     = null;
		s.lastCheckedIdx = -1;
		s.currentPage    = 1;
		s.totalCount     = 0;
		s.$fileGrid.addClass( 'mm-loading' ).empty();
		s.$folderHeading.text( '' );
		s.$folderCount.text( '' );
		$( '#mm-pagination' ).empty();
		$( document ).trigger( 'mm:folder-selected', [ folderId ] );

		if ( folderId === -1 ) {
			fetchOrphanPage( 1 );
		} else {
			fetchPage( folderId, 1 );
		}
	};

	/* -----------------------------------------------------------------------
	   Fetch: regular folder
	----------------------------------------------------------------------- */

	/**
	 * Fetch a page of files for a regular folder and render the grid.
	 *
	 * @param  {number} folderId  mm_folder post ID.
	 * @param  {number} page      1-based page number.
	 * @return {void}
	 */
	function fetchPage( folderId, page ) {
		s.$fileGrid.addClass( 'mm-loading' );
		s.lastCheckedIdx = -1;

		$.post( mm.url, {
			action:         'mm_folder_contents',
			nonce:          mm.nonce,
			folder_id:      folderId,
			sort_field:     s.currentSort.field,
			sort_direction: s.currentSort.direction,
			page:           page,
		}, function ( r ) {
			s.$fileGrid.removeClass( 'mm-loading' );
			if ( ! r.success ) {
				s.$fileGrid.html( '<p class="mm-error">' + lib.escHtml( ( r.data && r.data.message ) || 'Error' ) + '</p>' );
				$( '#mm-pagination' ).empty();
				return;
			}

			var d = r.data;
			s.currentSort    = { field: d.sort_field, direction: d.sort_direction };
			s.currentPage    = page;
			s.totalCount     = d.total;
			s.currentPerPage = d.per_page || 50;
			updateSortUI();

			if ( page === 1 ) {
				$.post( mm.url, { action: 'mm_load_folder', nonce: mm.nonce, folder_id: folderId }, function ( meta ) {
					if ( meta.success ) { s.$folderHeading.text( meta.data.name ); }
				} );
			}

			s.$folderCount.text( s.totalCount + ' ' + t( 'files', 'files' ) );
			renderGrid( d.files );
			renderPagination( s.currentPage, Math.ceil( s.totalCount / s.currentPerPage ) );

		} ).fail( function () {
			s.$fileGrid.removeClass( 'mm-loading' );
			s.$fileGrid.html( '<p class="mm-error">' + t( 'load_error', 'Could not load files.' ) + '</p>' );
			$( '#mm-pagination' ).empty();
		} );
	}

	/* -----------------------------------------------------------------------
	   Fetch: orphan attachments (virtual Unassigned node)
	----------------------------------------------------------------------- */

	/**
	 * Fetch a page of attachment files not assigned to any folder (orphans).
	 *
	 * @param  {number} page  1-based page number.
	 * @return {void}
	 */
	function fetchOrphanPage( page ) {
		s.$fileGrid.addClass( 'mm-loading' );
		s.lastCheckedIdx = -1;

		$.post( mm.url, { action: 'mm_get_orphans', nonce: mm.nonce, page: page }, function ( r ) {
			s.$fileGrid.removeClass( 'mm-loading' );
			if ( ! r.success ) {
				s.$fileGrid.html( '<p class="mm-error">' + lib.escHtml( ( r.data && r.data.message ) || 'Error' ) + '</p>' );
				$( '#mm-pagination' ).empty();
				return;
			}

			var d = r.data;
			s.currentPage    = page;
			s.totalCount     = d.total;
			s.currentPerPage = d.per_page || 50;
			s.$folderHeading.text( t( 'orphans_heading', 'Unassigned files' ) );
			s.$folderCount.text( s.totalCount + ' ' + t( 'files', 'files' ) );
			renderGrid( d.files );
			renderPagination( s.currentPage, Math.ceil( s.totalCount / s.currentPerPage ) );

		} ).fail( function () {
			s.$fileGrid.removeClass( 'mm-loading' );
			s.$fileGrid.html( '<p class="mm-error">' + t( 'load_error', 'Could not load files.' ) + '</p>' );
			$( '#mm-pagination' ).empty();
		} );
	}

	/* -----------------------------------------------------------------------
	   Fetch: recent-uploads filter
	----------------------------------------------------------------------- */

	/**
	 * Fetch a page of recently-uploaded attachments matching the active time filter.
	 *
	 * @param  {number} days  Number of days to look back.
	 * @param  {number} page  1-based page number.
	 * @return {void}
	 */
	lib.fetchFilterPage = function ( days, page ) {
		s.$fileGrid.addClass( 'mm-loading' );
		s.lastCheckedIdx = -1;

		$.post( mm.url, { action: 'mm_recent_files', nonce: mm.nonce, days: days, page: page }, function ( r ) {
			s.$fileGrid.removeClass( 'mm-loading' );
			if ( ! r.success ) {
				s.$fileGrid.html( '<p class="mm-error">' + lib.escHtml( ( r.data && r.data.message ) || 'Error' ) + '</p>' );
				$( '#mm-pagination' ).empty();
				return;
			}

			var d     = r.data;
			var label = days === 7 ? t( 'last_7_days', 'Last 7 days' ) : t( 'last_30_days', 'Last 30 days' );
			s.currentPage    = page;
			s.totalCount     = d.total;
			s.currentPerPage = d.per_page || 50;
			s.$folderHeading.text( t( 'recent_heading', 'Recent uploads' ) + ' \u2014 ' + label );
			s.$folderCount.text( s.totalCount + ' ' + t( 'files', 'files' ) );
			renderGrid( d.files );
			renderPagination( s.currentPage, Math.ceil( s.totalCount / s.currentPerPage ) );

		} ).fail( function () {
			s.$fileGrid.removeClass( 'mm-loading' );
			s.$fileGrid.html( '<p class="mm-error">' + t( 'load_error', 'Could not load files.' ) + '</p>' );
			$( '#mm-pagination' ).empty();
		} );
	};

	/* -----------------------------------------------------------------------
	   Grid render
	----------------------------------------------------------------------- */

	/**
	 * Render the file grid from a server-supplied file list.
	 * Clears previous content and rebinds checkbox/click handlers.
	 *
	 * @param  {Array<object>} files  File data objects from the AJAX response.
	 * @return {void}
	 */
	function renderGrid( files ) {
		s.$fileGrid.empty();
		s.lastCheckedIdx = -1;
		lib.updateSelectAll();

		if ( ! files || ! files.length ) {
			s.$fileGrid.html( '<p class="mm-empty">' + t( 'no_files', 'No files in this folder.' ) + '</p>' );
			return;
		}

		var html = '';
		for ( var i = 0; i < files.length; i++ ) {
			html += lib.renderFileItem( files[ i ], i );
		}

		s.$fileGrid.html( html );
		s.$fileGrid.off( 'change', '.mm-file-checkbox' ).on( 'change', '.mm-file-checkbox', lib.handleCheckboxChange );
		s.$fileGrid.off( 'click',  '.mm-file-item'     ).on( 'click',  '.mm-file-item',     lib.handleItemClick );
	}

	/**
	 * Build and return the HTML string for a single file grid item.
	 *
	 * @param  {object} file   File data object (id, is_image, thumbnail, title, mime, filename, edit_url).
	 * @param  {number} index  Zero-based position in the current page, used for shift-click range selection.
	 * @return {string}        HTML string for the .mm-file-item element.
	 */
	lib.renderFileItem = function ( file, index ) {
		var media;
		if ( file.is_image && file.thumbnail ) {
			media = '<img src="' + lib.escAttr( file.thumbnail ) + '" alt="' + lib.escAttr( file.title ) + '" loading="lazy">';
		} else {
			var icon = s.mimeIcons[ file.mime ] || 'dashicons-media-default';
			media = '<span class="dashicons ' + icon + ' mm-file-icon"></span>';
		}

		return '<div class="mm-file-item" data-id="' + file.id + '" data-index="' + index + '" data-is-image="' + ( file.is_image ? '1' : '0' ) + '">' +
			'<label class="mm-file-check-wrap"><input type="checkbox" class="mm-file-checkbox" value="' + file.id + '" data-index="' + index + '"></label>' +
			'<a class="mm-file-thumb" href="' + lib.escAttr( file.edit_url || '#' ) + '" target="_blank" rel="noopener">' + media + '</a>' +
			'<div class="mm-file-meta"><span class="mm-file-name" title="' + lib.escAttr( file.filename ) + '">' + lib.escHtml( file.filename ) + '</span></div>' +
		'</div>';
	};

	/* -----------------------------------------------------------------------
	   Pagination
	----------------------------------------------------------------------- */

	/**
	 * Bind click handlers for pagination buttons.
	 * Dispatches the correct fetch function based on the current filter/folder state.
	 *
	 * @return {void}
	 */
	lib.initPagination = function () {
		$( document ).on( 'click', '.mm-page-btn:not([disabled])', function () {
			var page = parseInt( $( this ).data( 'page' ), 10 );
			if ( ! page || page < 1 ) { return; }
			s.$fileGrid.find( '.mm-file-checkbox' ).prop( 'checked', false );
			s.$fileGrid.find( '.mm-file-item' ).removeClass( 'mm-selected' );
			lib.updateSelectAll();
			lib.updateBulkToolbar();

			if ( s.filterMode === 'recent' ) {
				lib.fetchFilterPage( s.filterDays, page );
			} else if ( s.activeFolderId === -1 ) {
				fetchOrphanPage( page );
			} else if ( s.activeFolderId ) {
				fetchPage( s.activeFolderId, page );
			}
		} );
	};

	/**
	 * Render prev/next/numbered pagination buttons into #mm-pagination.
	 *
	 * @param  {number} page        Current 1-based page number.
	 * @param  {number} totalPages  Total number of pages.
	 * @return {void}
	 */
	function renderPagination( page, totalPages ) {
		var $pg = $( '#mm-pagination' );
		if ( ! $pg.length || totalPages <= 1 ) { $pg.empty(); return; }

		var pages = pagesToShow( page, totalPages );
		var prev  = null;
		var html  = '';

		html += '<button class="button mm-page-btn" data-page="' + ( page - 1 ) + '"' + ( page === 1 ? ' disabled' : '' ) + '>&#8249;</button>';

		for ( var i = 0; i < pages.length; i++ ) {
			var p = pages[ i ];
			if ( prev !== null && p > prev + 1 ) { html += '<span class="mm-page-ellipsis">&hellip;</span>'; }
			html += '<button class="button mm-page-btn' + ( p === page ? ' button-primary' : '' ) + '" data-page="' + p + '">' + p + '</button>';
			prev = p;
		}

		html += '<button class="button mm-page-btn" data-page="' + ( page + 1 ) + '"' + ( page === totalPages ? ' disabled' : '' ) + '>&#8250;</button>';
		$pg.html( html );
	}

	/**
	 * Return the page numbers to display, always including first, last, and a window
	 * of 2 pages around the current page. Gaps are rendered as ellipses by the caller.
	 *
	 * @param  {number} current  Current 1-based page number.
	 * @param  {number} total    Total number of pages.
	 * @return {number[]}        Sorted, deduplicated page numbers to show.
	 */
	function pagesToShow( current, total ) {
		var pages = [], p;
		for ( p = 1; p <= total; p++ ) {
			if ( p === 1 || p === total || ( p >= current - 2 && p <= current + 2 ) ) { pages.push( p ); }
		}
		return pages;
	}

	/* -----------------------------------------------------------------------
	   Sort controls
	----------------------------------------------------------------------- */

	/**
	 * Bind click handlers for column sort buttons and trigger a re-fetch on change.
	 *
	 * @return {void}
	 */
	lib.initSortControls = function () {
		$( document ).on( 'click', '.mm-sort-btn', function () {
			var field = $( this ).data( 'field' );
			var dir   = ( s.currentSort.field === field && s.currentSort.direction === 'ASC' ) ? 'DESC' : 'ASC';
			s.currentSort = { field: field, direction: dir };
			updateSortUI();

			if ( s.filterMode === 'recent' ) {
				// Recent filter: re-fetch page 1 so the server-side ordering
				// (WP_Query orderby=date) is applied fresh. Client-side sort
				// would lose items across pages.
				lib.fetchFilterPage( s.filterDays, 1 );
			} else if ( s.activeFolderId === -1 ) {
				// Orphan list: re-fetch page 1 (server sorts by post_date).
				fetchOrphanPage( 1 );
			} else if ( s.activeFolderId ) {
				$.post( mm.url, {
					action:         'mm_sort_contents',
					nonce:          mm.nonce,
					folder_id:      s.activeFolderId,
					sort_field:     field,
					sort_direction: dir,
				}, function ( r ) {
					if ( r.success ) {
						renderGrid( r.data.files );
						s.$folderCount.text( r.data.total + ' ' + t( 'files', 'files' ) );
					}
				} );
			}
		} );
	};

	/**
	 * Update sort button CSS classes to reflect the current sort field and direction.
	 *
	 * @return {void}
	 */
	function updateSortUI() {
		$( '.mm-sort-btn' ).each( function () {
			var $btn = $( this ), f = $btn.data( 'field' );
			$btn.removeClass( 'mm-sort-asc mm-sort-desc mm-sort-active' );
			if ( f === s.currentSort.field ) {
				$btn.addClass( 'mm-sort-active ' + ( s.currentSort.direction === 'ASC' ? 'mm-sort-asc' : 'mm-sort-desc' ) );
			}
		} );
	}

	/* -----------------------------------------------------------------------
	   Grid notices
	----------------------------------------------------------------------- */

	/**
	 * Insert a temporary notice banner below the content header, auto-dismissing after 6 s.
	 *
	 * @param  {string} message  Text to display.
	 * @param  {string} [type]   WP admin notice type: 'success', 'warning', 'error', or 'info' (default).
	 * @return {void}
	 */
	lib.showGridNotice = function ( message, type ) {
		type = type || 'info';
		var $notice = $( '<div class="notice notice-' + type + ' is-dismissible mm-grid-notice"><p>' + lib.escHtml( message ) + '</p></div>' );
		$( '#mm-content-header' ).after( $notice );
		setTimeout( function () { $notice.slideUp( 300, function () { $( this ).remove(); } ); }, 6000 );
	};

}( jQuery, window.mmLib ) );
