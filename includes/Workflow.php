<?php

namespace ArticleCreationWorkflow;

use Config;
use EditPage;
use Message;

/**
 * Contains this extension's business logic
 */
class Workflow {
	const LANDING_PAGE = 'article-creation-landing-page';

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
	 * @return Message|null
	 */
	public function getLandingPageMessage() {
		$msg = wfMessage( self::LANDING_PAGE )->inContentLanguage();
		if ( $msg->isDisabled() ) {
			return null;
		}

		return $msg;
	}

	/**
	 * Checks whether an attempt to edit a page should be intercepted and redirected to our workflow
	 *
	 * @param EditPage $editPage
	 * @return bool
	 */
	public function shouldInterceptEditPage( EditPage $editPage ) {
		$title = $editPage->getTitle();
		$user = $editPage->getContext()->getUser();

		$conditions = $this->config->get( 'ArticleCreationWorkflows' );

		// We are only interested in creation
		if ( $title->exists() ) {
			return false;
		}

		// Don't intercept if the landing page is not configured
		$landingPage = $this->getLandingPageMessage();
		if ( !$landingPage ) {
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
