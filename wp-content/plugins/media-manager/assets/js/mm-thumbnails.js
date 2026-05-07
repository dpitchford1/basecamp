/* ==========================================================================
   mm-thumbnails.js  —  Media Manager: Thumbnail Regeneration page
   Phase 11. Depends on: jQuery, mm_ajax.
   ========================================================================== */

( function ( $, mm ) {
	'use strict';

	var totalChunks  = 0;
	var currentChunk = 0;
	var totalFiles   = 0; 

	$( document ).ready( function () {
		if ( ! $( '#mm-regen-start' ).length ) { return; }

		$( '#mm-regen-start' ).on( 'click', startRegen );
	} );

	function startRegen() {
		$( '#mm-regen-start' ).prop( 'disabled', true );
		$( '#mm-regen-progress' ).show();
		setProgress( 0, t( 'preparing', 'Preparing…' ) );

		$.post( mm.url, {
			action:    'mm_regen_thumbnails',
			nonce:     mm.nonce,
			folder_id: $( '#mm-regen-folder' ).val() || 0,
		}, function ( r ) {
			if ( ! r.success ) {
				setProgress( 0, ( r.data && r.data.message ) || t( 'error', 'Error' ) );
				$( '#mm-regen-start' ).prop( 'disabled', false );
				return;
			}
			totalFiles  = r.data.total;
			totalChunks = r.data.chunks;
			currentChunk = 0;

			if ( totalFiles === 0 ) {
				setProgress( 100, t( 'no_images', 'No images to regenerate.' ) );
				$( '#mm-regen-start' ).prop( 'disabled', false );
				return;
			}

			processChunk();
		} );
	}

	function processChunk() {
		$.post( mm.url, {
			action:      'mm_regen_process',
			nonce:       mm.nonce,
			chunk_index: currentChunk,
		}, function ( r ) {
			if ( ! r.success ) {
				setProgress( getPercent(), ( r.data && r.data.message ) || t( 'error', 'Error' ) );
				$( '#mm-regen-start' ).prop( 'disabled', false );
				return;
			}

			currentChunk++;
			var pct   = getPercent();
			var label = pct + '% — ' + t( 'remaining', 'remaining' ) + ': ' + r.data.remaining;
			setProgress( pct, label );

			if ( r.data.remaining > 0 ) {
				processChunk();
			} else {
				setProgress( 100, t( 'done', 'Done!' ) );
				$( '#mm-regen-start' ).prop( 'disabled', false );
			}
		} );
	}

	function getPercent() {
		return totalChunks > 0 ? Math.min( 100, Math.round( currentChunk / totalChunks * 100 ) ) : 100;
	}

	function setProgress( pct, label ) {
		$( '#mm-regen-bar' ).css( 'width', pct + '%' );
		$( '#mm-regen-label' ).text( label );
	}

	function t( key, fallback ) {
		return ( mm.i18n && mm.i18n[ key ] ) ? mm.i18n[ key ] : fallback;
	}

}( jQuery, window.mm_ajax || {} ) );
