/*
 Track link clicks on Special:CreatePage
 */

( function ( $, mw ) {

	function trackData( interactionType, link, sampling ) {
		mw.loader.using( 'schema.ArticleCreationWorkflow' ).then( function () {
			mw.eventLog.logEvent( 'ArticleCreationWorkflow', {
				interactionType: interactionType,
				link: link,
				sampling: sampling ? sampling : 1
			} );
		} );
	}

	$( '#bodyContent' ).find( 'a' ).click( function ( event ) {
		var $link = $( this ).attr( 'href' );
		trackData( 'click', $link );
	} );

} ( jQuery, mediaWiki ) );
