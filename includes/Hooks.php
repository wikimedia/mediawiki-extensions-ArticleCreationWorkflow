<?php

namespace ArticleCreationWorkflow;

use MediaWiki\MediaWikiServices;
use Article;
use User;

/**
 * Hook handlers
 */
class Hooks {
	/**
	 * CustomEditor hook handler
	 * Redirects users attempting to create pages to the landing page, based on configuration
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CustomEditor
	 *
	 * @param Article $article The requested page
	 * @param User $user The user trying to load the editor
	 * @return bool
	 */
	public static function onCustomEditor( Article $article, User $user ) {
		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'ArticleCreationWorkflow' );
		$workflow = new Workflow( $config );

		if ( $workflow->shouldInterceptEditPage( $article, $user ) ) {
			$title = $article->getTitle();
			// If the landing page didn't exist, we wouldn't have intercepted.
			$redirTo = $workflow->getLandingPageTitle();
			$output = $article->getContext()->getOutput();
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
		$user = $article->getContext()->getUser();
		if ( $workflow->shouldInterceptEditPage( $article, $user ) &&
			!$user->isAnon()
		) {
			$title = $article->getTitle();
			// If the landing page didn't exist, we wouldn't have intercepted.
			$redirTo = $workflow->getLandingPageTitle();
			$output = $article->getContext()->getOutput();
			$output->redirect( $redirTo->getFullURL(
				[ 'page' => $title->getPrefixedText(), 'wprov' => 'acww1' ]
			) );
		}
	}
}
