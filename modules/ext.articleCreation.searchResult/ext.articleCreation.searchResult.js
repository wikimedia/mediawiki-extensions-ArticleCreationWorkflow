jQuery( document ).ready( function() {
	var newTitle = 'Special:ArticleCreationLanding' + '/' +
		encodeURIComponent(mw.config.get('acSearch'));
	var landingURL = mw.config.get('wgArticlePath').replace( '$1', newTitle );
	// change the link to point to the new special page
	jQuery("div.searchresults")
		.find('a[href*="action=edit"]')
		.attr( 'href', landingURL );
} );
