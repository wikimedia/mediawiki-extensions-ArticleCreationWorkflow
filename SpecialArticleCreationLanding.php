<?php
/**
 * Special:ArticleCreationLanding. Special page for Artcile creation landing page.
 */
class SpecialArticleCreationLanding extends SpecialPage {

	public function __construct() {
		//Do not list this special page under Special:SpecialPages
		parent::__construct( 'ArticleCreationLanding', '', false );
		$this->pageTitle = null;
	}
	
	public function getDescription() {
		return wfMessage( $this->pageTitle )->plain();
	}

	public function execute( $par ) {
		global $wgOut, $wgUser, $wgRequest;

		$title = Title::newFromText( $par );

		// bad title 
		if ( !$title instanceof Title ) {
			$title = Title::newMainPage();
		}
		// title exists
		if ( $title->exists() ) {
			$wgOut->redirect( $title->getFullURL() );
			return;
		}
		
		$this->pageTitle = wfMsg( 'ac-landing-page-title', $title );
		$wgOut->setPageTitle( $this->pageTitle );
		$wgOut->setRobotPolicy( 'noindex,nofollow' );
		$wgOut->addModules( 'ext.articleCreation.core' );
		$wgOut->addModules( 'ext.articleCreation.user' );
		$wgOut->addHtml( ArticleCreationTemplates::getLandingPage($par) );

		ArticleCreationUtil::TrackSpecialLandingPage( $wgRequest, $wgUser, $par );		
	}
	
}
