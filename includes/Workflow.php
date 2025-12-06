<?php

namespace ArticleCreationWorkflow;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Language\RawMessage;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Contains this extension's business logic
 */
class Workflow {

	public function __construct( private readonly Config $config ) {
	}

	/**
	 * @return Config
	 */
	public function getConfig() {
		return $this->config;
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
	 * Check whether an attempt to visit a missing page should be intercepted and replaced by our
	 * workflow.
	 *
	 * @param Title $title The title $user attempts to create
	 * @param User $user The user trying to load the editor
	 * @return bool
	 */
	public function shouldInterceptPage( Title $title, User $user ) {
		// We are only interested in creation
		if ( $title->exists() ) {
			return false;
		}

		// Articles only
		if ( !$title->inNamespace( NS_MAIN ) ) {
			return false;
		}

		// User has perms, don't intercept
		if ( $user->isAllowed( 'createpagemainns' ) ) {
			return false;
		}

		// Only intercept users who can potentially create articles otherwise
		if ( !$user->isAllowed( 'createpage' ) ) {
			return false;
		}

		// Don't intercept if the landing page is not configured
		$landingPage = $this->getLandingPageTitle();
		if ( $landingPage === null || !$landingPage->exists() ) {
			return false;
		}

		return true;
	}

	/**
	 * If a user without sufficient permissions attempts to view or create a missing page in the main
	 * namespace, display our workflow instead with a message defined on-wiki.
	 *
	 * @param Title $title
	 * @param User $user
	 * @param IContextSource $context
	 * @return bool Whether we intercepted the page view by displaying our own message
	 */
	public function interceptIfNeeded( Title $title, User $user, IContextSource $context ) {
		if ( $this->shouldInterceptPage( $title, $user ) ) {
			// If the landing page didn't exist, we wouldn't have intercepted, so it's guaranteed to exist
			// here ($landingPage is not null).
			$landingPage = $this->getLandingPageTitle();
			$output = $context->getOutput();
			$output->disableClientCache();

			// Transclude the landing page instead of redirecting. This allows for the deletion log snippet
			// to be shown as usual, and for magic words like {{PAGENAME}} to be used in the message. (T204234)
			$msg = new RawMessage( '{{:' . $landingPage->getPrefixedText() . '}}' );
			$msg->page( $title );
			$output->addHTML( $msg->parseAsBlock() );

			return true;
		}

		return false;
	}
}
