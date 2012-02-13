(function($,mw) {
	var newTitle = 'Special:ArticleCreationLanding' + '/' +
		encodeURIComponent(mw.config.get('acSearch'));
	var landingURL = mw.config.get('wgArticlePath').replace( '$1', newTitle );
	// change the link to point to the new special page
	$("div.searchresults")
		.find('a[href*="action=edit"]')
		.attr( 'href', landingURL );
})( jQuery, window.mediaWiki );
