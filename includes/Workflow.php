<?php

namespace ArticleCreationWorkflow;

use Config;
use EditPage;

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
	 * Checks whether an attempt to edit a page should be intercepted and redirected to our workflow
	 *
	 * @param EditPage $editPage
	 * @return bool
	 */
	public function shouldInterceptEditPage( EditPage $editPage ) {
		$title = $editPage->getTitle();
		$user = $editPage->getContext()->getUser();
		$request = $editPage->getContext()->getRequest();

		$conditions = $this->config->get( 'ArticleCreationWorkflows' );

		// We are only interested in creation
		if ( $title->exists() ) {
			return false;
		}

		foreach ( $conditions as $cond ) {
			// Filter on namespace
			if ( !in_array( $title->getNamespace(), $cond['namespaces'] ) ) {
				continue;
			}

			// Filter out users who don't have these rights
			if ( isset( $cond['redirectRight'] ) && !$user->isAllowed( $cond['redirectRight'] ) ) {
				continue;
			}

			// Filter out people who have these rights
			if ( isset( $cond['excludeRight'] ) && $user->isAllowed( $cond['excludeRight'] ) ) {
				continue;
			}

			return true;
		}

		return false;
	}
}
