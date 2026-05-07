/* ==========================================================================
   mm-library.js  —  Media Manager: Library page — bootstrap / namespace
   Defines window.mmLib (shared state + utilities).
   Logic lives in: mm-library-tree.js, mm-library-grid.js, mm-library-files.js
   Depends on: jQuery, jsTree 3.x, mm_ajax (localised by Assets)
   ========================================================================== */

( function ( $, mm ) {
	'use strict'; 

	/* -----------------------------------------------------------------------
	   Shared namespace
	----------------------------------------------------------------------- */

	var lib = window.mmLib = {
		mm: mm,

		/* Shared mutable state — sub-modules read/write via lib.state */
		state: {
			$tree:             null,
			$folderHeading:    null,
			$fileGrid:         null,
			$folderCount:      null,
			$selectAll:        null,

			activeFolderId:    0,
			lastCheckedIdx:    -1,
			currentSort:       { field: 'date', direction: 'DESC' },
			protectedFolderIds: {},

			currentPage:       1,
			totalCount:        0,
			currentPerPage:    50,

			filterMode:        null,   // null | 'recent'
			filterDays:        7,

			draggedIds:        [],

			mimeIcons: {
				'application/pdf':     'dashicons-pdf',
				'application/zip':     'dashicons-media-archive',
				'audio/mpeg':          'dashicons-media-audio',
				'audio/ogg':           'dashicons-media-audio',
				'video/mp4':           'dashicons-media-video',
				'video/quicktime':     'dashicons-media-video',
				'text/plain':          'dashicons-media-text',
				'application/msword':  'dashicons-media-document',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'dashicons-media-document',
				'application/vnd.ms-excel': 'dashicons-media-spreadsheet',
			},
		},

		/* Utilities */

		/**
		 * Return a translated string from mm_ajax.i18n, falling back to the provided default.
		 *
		 * @param  {string} key      i18n key passed from wp_localize_script.
		 * @param  {string} fallback Default text used when the key is absent.
		 * @return {string}
		 */
		t: function ( key, fallback ) {
			return ( mm.i18n && mm.i18n[ key ] ) ? mm.i18n[ key ] : fallback;
		},

		/**
		 * Escape a string for safe insertion as HTML text content.
		 *
		 * @param  {*}      str  Value to escape. Coerced to string.
		 * @return {string}      HTML-safe string.
		 */
		escHtml: function ( str ) {
			return $( '<span>' ).text( String( str ) ).html();
		},

		/**
		 * Escape a string for safe insertion into an HTML attribute value.
		 *
		 * @param  {*}      str  Value to escape. Coerced to string.
		 * @return {string}      Attribute-safe string.
		 */
		escAttr: function ( str ) {
			return String( str )
				.replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' )
.replace( /'/g, '&#039;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
},
};

/* -----------------------------------------------------------------------
   Boot — runs after DOM ready; sub-modules must be loaded before this
----------------------------------------------------------------------- */

$( document ).ready( function () {
var s = lib.state;

s.$tree          = $( '#mm-folder-tree' );
s.$folderHeading = $( '#mm-folder-heading' );
s.$fileGrid      = $( '#mm-file-grid' );
s.$folderCount   = $( '#mm-folder-count' );
s.$selectAll     = $( '#mm-select-all' );

if ( ! s.$tree.length ) { return; }

lib.initTree();
lib.initSortControls();
lib.initSelectAll();
lib.initBulkActions();
lib.initRename();
lib.initDragDrop();
lib.initSync();
lib.initFolderActions();
lib.initUploadListener();
lib.initAttachmentEdit();
lib.initPagination();
lib.initRecentFilter();
lib.initFolderPreview();
} );

}( jQuery, window.mm_ajax || {} ) );
