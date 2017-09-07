<?php

namespace ArticleCreationWorkflow;

use EditPage;
use MediaWiki\MediaWikiServices;
use Article;

/**
 * Hook handlers
 */
class Hooks {
	/**
	 * AlternateEdit hook handler
	 * Redirects users attempting to create pages to the landing page, based on configuration
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

	/**
	 * ShowMissingArticle hook handler
	 * If article doesn't exist, redirect non-autoconfirmed users to  AfC
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ShowMissingArticle
	 *
	 * @param Article $article Article instance
	 * @return bool
	 */
	public static function onShowMissingArticle( Article $article ) {
		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'ArticleCreationWorkflow' );
		$workflow = new Workflow( $config );
		$editPage = new EditPage( $article );
		if ( $workflow->shouldInterceptEditPage( $editPage ) &&
			!$editPage->getContext()->getUser()->isAnon()
		) {
			$title = $editPage->getTitle();
			// If the landing page didn't exist, we wouldn't have intercepted.
			$redirTo = $workflow->getLandingPageTitle();
			$output = $editPage->getContext()->getOutput();
			$output->redirect( $redirTo->getFullURL(
				[ 'page' => $title->getPrefixedText(), 'wprov' => 'acww1' ]
			) );
		}
	}
}
