<?php

namespace ArticleCreationWorkflow;

use Config;
use Article;
use User;
use Title;

/**
 * Contains this extension's business logic
 */
class Workflow {

	/** @var Config */
	private $config;

	/**
	 * @param Config $config Configuration to use
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Returns the message defining the landing page
	 *
	 * @return Title|null
	 */
	public function getLandingPageTitle() {
		$landingPageName = $this->config->get( 'ArticleCreationLandingPage' );
		return Title::newFromText( $landingPageName );
	}

	/**
	 * Checks whether an attempt to edit a page should be intercepted and redirected to our workflow
	 *
	 * @param Article $article The requested page
	 * @param User $user The user trying to load the editor
	 * @return bool
	 */
	public function shouldInterceptEditPage( Article $article, User $user ) {
		$title = $article->getTitle();

		$conditions = $this->config->get( 'ArticleCreationWorkflows' );

		// We are only interested in creation
		if ( $title->exists() ) {
			return false;
		}

		// Don't intercept if the landing page is not configured
		$landingPage = $this->getLandingPageTitle();
		if ( $landingPage === null || !$landingPage->exists() ) {
			return false;
		}

		foreach ( $conditions as $cond ) {
			// Filter on namespace
			if ( !in_array( $title->getNamespace(), $cond['namespaces'] ) ) {
				continue;
			}

			// Don't intercept users that have these rights
			if ( isset( $cond['excludeRight'] ) && $user->isAllowed( $cond['excludeRight'] ) ) {
				continue;
			}

			return true;
		}

		return false;
	}
}
