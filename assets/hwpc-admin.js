/**
 * Admin behaviour for the Element paging condition meta box.
 *
 * Shows the page list field only while the "Only show on pages…" condition is
 * selected. The field is rendered with the correct visibility server-side, so
 * this only has to keep up with the visitor changing their mind.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var container = document.querySelector( '.' + hwpcData.containerClass );

		if ( ! container ) {
			return;
		}

		var pagesRow = container.querySelector( '.hwpc-pages' );

		if ( ! pagesRow ) {
			return;
		}

		container.addEventListener( 'change', function ( event ) {
			if ( 'radio' === event.target.type ) {
				pagesRow.hidden = hwpcData.onlyPagesCondition !== event.target.value;
			}
		} );
	} );
}() );
