<?php

namespace ArticleCreationWorkflow;

use EditPage;
use MediaWiki\MediaWikiServices;

/**
 * Hook handlers
 */
class Hooks {
	/**
	 * AlternateEdit hook handler
	 * Redirects users attempting to create pages to Special:CreatePage, based on configuration
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/AlternateEdit
	 *
	 * @param EditPage $editPage
	 * @return bool
	 */
	public static function onAlternateEdit( EditPage $editPage ) {
		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'ArticleCreationWorkflow' );
		$workflow = new Workflow( $config );

		if ( $workflow->shouldInterceptEditPage( $editPage ) ) {
			$title = $editPage->getTitle();
			// If the landing page didn't exist, we wouldn't have intercepted.
			$redirTo = $workflow->getLandingPageTitle();
			$output = $editPage->getContext()->getOutput();
			$output->redirect( $redirTo->getFullURL(
				[ 'page' => $title->getPrefixedText(), 'wprov' => 'acww1' ]
			) );

			return false;
		}

		return true;
	}
}
