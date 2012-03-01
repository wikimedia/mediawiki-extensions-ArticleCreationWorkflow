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
		$out = $this->getOutput();
		$title = Title::newFromText( $par );

		// bad title 
		if ( !$title instanceof Title ) {
			$title = Title::newMainPage();
		}
		// title exists
		if ( $title->exists() ) {
			$out->redirect( $title->getFullURL() );
			return;
		}
		
		$this->pageTitle = wfMsg( 'ac-landing-page-title', $title );
		$out->setPageTitle( $this->pageTitle );
		$out->setRobotPolicy( 'noindex,nofollow' );
		$out->addModules( 'ext.articleCreation.core' );
		$out->addModules( 'ext.articleCreation.user' );
		$out->addHtml( ArticleCreationTemplates::getLandingPage($par) );

		ArticleCreationUtil::TrackSpecialLandingPage( $par );
	}
	
}
