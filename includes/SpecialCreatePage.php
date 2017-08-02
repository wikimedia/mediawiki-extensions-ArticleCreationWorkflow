<?php

namespace ArticleCreationWorkflow;

use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use Title;
use TitleValue;
use UnlistedSpecialPage;

/**
 * Special:CreatePage code
 */
class SpecialCreatePage extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'CreatePage' );
	}

	/**
	 * Returns the name that goes in the \<h1\> in the special page itself, and
	 * also the name that will be listed in Special:Specialpages.
	 * @return string
	 */
	function getDescription() {
		return $this->msg( 'acw-createpage' )->text();
	}

	/**
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'ArticleCreationWorkflow' );
		$workflow = new Workflow( $config );

		$destTitle = Title::newFromText( $subPage );
		$destTitleText = $destTitle ? $destTitle->getPrefixedText() : '';
		$landingPageMessage = $workflow->getLandingPageMessage()->params( $destTitleText );

		// If the landing page is not configured, show an error message.
		if ( !$landingPageMessage ) {
			$landingPageTitle = new TitleValue( NS_MEDIAWIKI, Workflow::LANDING_PAGE );
			$msgLink = MediaWikiServices::getInstance()
				->getLinkRenderer()
				->makeBrokenLink( $landingPageTitle, Workflow::LANDING_PAGE );
			$err = wfMessage( 'acw-no-landing-page' )->rawParams( $msgLink );
			$this->getOutput()->addHTML( '<p class="error">' . $err->parse() . '</p>' );
			return;
		}

		$this->getOutput()->addWikiText( $landingPageMessage->text() );
	}
}
