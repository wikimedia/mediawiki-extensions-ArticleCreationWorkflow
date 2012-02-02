<?php
/**
 * Special:ArticleCreationLanding. Special page for Artcile creation landing page.
 */
class SpecialArticleCreationLanding extends SpecialPage {

	public function __construct() {
		//Do not list this special page under Special:SpecialPages
		parent::__construct( 'ArticleCreationLanding', '', false );
	}
	
	public function getDescription() {
		return wfMessage( 'ac-landing-page-title' )->plain();
	}

	public function execute( $par ) {
		global $wgOut;
		
		$wgOut->setRobotPolicy( 'noindex,nofollow' );
		$wgOut->addModules( 'ext.articleCreation.core' );
		$wgOut->addModules( 'ext.articleCreation.user' );
		$wgOut->addHtml( ArticleCreationTemplates::loadArticleCreationModules() );
	}
	
}
