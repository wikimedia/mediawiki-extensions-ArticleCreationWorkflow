<?php

namespace ArticleCreationWorkflow;

use EditPage;
use MediaWiki\MediaWikiServices;
use SpecialPage;

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
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$workflow = new Workflow( $config );

		if ( $workflow->shouldInterceptEditPage( $editPage ) ) {
			$title = $editPage->getTitle();
			$redirTo = SpecialPage::getTitleFor( 'CreatePage', $title->getPrefixedText() );
			$output = $editPage->getContext()->getOutput();
			$output->redirect( $redirTo->getFullURL() );

			return false;
		}

		return true;
	}
}
