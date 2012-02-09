(function($,mw) {
	var newTitle = 'Special:ArticleCreationLanding' + '/' +
		encodeURIComponent(wgPageName);
	var landingURL = mw.config.get('wgArticlePath').replace( '$1', newTitle );
	// change the link to point to the new special page
	$("div.noarticletext")
		.find('a[href*="action=edit"]')
		.attr( 'href', landingURL );
})( jQuery, window.mediaWiki );
