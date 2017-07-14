<?php

namespace ArticleCreationWorkflow\Tests;

use Article;
use ArticleCreationWorkflow\Workflow;
use DerivativeContext;
use EditPage;
use FauxRequest;
use HashConfig;
use MediaWikiTestCase;
use RequestContext;
use Title;
use User;

/**
 * @group ArticleCreationWorkflow
 */
class WorkflowTest extends MediaWikiTestCase {
	/**
	 * @dataProvider provideShouldInterceptEditPage
	 *
	 * @param User $user
	 * @param Title $title
	 * @param array $settings
	 * @param string $query
	 * @param bool $expected
	 */
	public function testShouldInterceptEditPage( User $user, Title $title,
		$settings, $expected
	) {
		$context = new DerivativeContext( RequestContext::getMain() );
		$context->setTitle( $title );
		$context->setUser( $user );

		$article = new Article( $title );
		$article->setContext( $context );
		$editPage = new EditPage( $article );
		$config = new HashConfig( [ 'ArticleCreationWorkflows' => $settings ] );

		$workflow = new Workflow( $config );

		self::assertEquals( $expected, $workflow->shouldInterceptEditPage( $editPage ) );
	}

	public function provideShouldInterceptEditPage() {
		$anon = User::newFromId( 0 );
		$newbie = $this->getMock( 'User' );
		$newbie->method( 'isAllowed' )
			->with( 'autoconfirmed' )
			->willReturn( false );
		$confirmed = $this->getMock( 'User' );
		$confirmed->method( 'isAllowed' )
			->with( 'autoconfirmed' )
			->willReturn( true );

		$mainspacePage = Title::newFromText( 'Some nonexistent page' );
		$miscPage = Title::newFromText( 'Project:Nonexistent too' );
		$existingPage = $this->getMock( 'Title' );
		$existingPage->method( 'exists' )
			->willReturn( true );
		$existingPage->method( 'getContentModel' )
			->willReturn( CONTENT_MODEL_WIKITEXT );

		$config = [
			[
				'namespaces' => [ NS_MAIN ],
				'excludeRight' => 'autoconfirmed',
			],
		];

		return [
			// No config, do nothing
			[ $anon, $mainspacePage, [], false ],
			// Wrong NS, do nothing
			[ $anon, $miscPage, $config, false ],
			// Page exists, do nothing
			[ $anon, $existingPage, $config, false ],
			// Confirmed user, do nothing
			[ $confirmed, $mainspacePage, $config, false ],

			// Anon attempting to create a page, intercept
			[ $anon, $mainspacePage, $config, true ],
			// Newbie attempting to create a page, intercept
			[ $newbie, $mainspacePage, $config, true ],
		];
	}
}
