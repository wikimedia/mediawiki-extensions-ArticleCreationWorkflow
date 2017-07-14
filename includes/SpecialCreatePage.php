<?php

namespace ArticleCreationWorkflow;

use Exception;
use UnlistedSpecialPage;

/**
 * Special:CreatePage code
 */
class SpecialCreatePage extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'CreatePage' );
	}

	/**
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		throw new Exception( 'Not implemented' );
	}
}
