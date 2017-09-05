<?php

namespace ArticleCreationWorkflow\Tests;

use Article;
use ArticleCreationWorkflow\Workflow;
use DerivativeContext;
use EditPage;
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

		$landingPage = $this->getMock( Title::class, [ 'exists' ] );
		$landingPage->method( 'exists' )->willReturn( true );
		$workflow = $this->getMock( Workflow::class, [ 'getLandingPageTitle' ], [ $config ] );
		$workflow->method( 'getLandingPageTitle' )->willReturn( $landingPage );

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
			[ $anon, $mainspacePage, [], false, 'No config, do nothing' ],
			[ $anon, $miscPage, $config, false, 'Wrong NS, do nothing' ],
			[ $anon, $existingPage, $config, false, 'Page exists, do nothing' ],
			[ $confirmed, $mainspacePage, $config, false, 'Confirmed user, do nothing' ],

			[ $anon, $mainspacePage, $config, true, 'Anon attempting to create a page, intercept' ],
			[ $newbie, $mainspacePage, $config, true, 'Newbie attempting to create a page, intercept' ],
		];
	}

	public function testLandingPageExistence() {
		$article = new Article( Title::newFromText( 'Test page' ) );
		$editPage = new EditPage( $article );
		$config = new HashConfig( [
			'ArticleCreationWorkflows' => [
				[
					'namespaces' => [ NS_MAIN ],
					'excludeRight' => 'autoconfirmed',
				],
			],
			'ArticleCreationLandingPage' => 'Non existant page',
		] );

		$workflow = $this->getMock( Workflow::class, [ 'getLandingPageTitle' ], [ $config ] );
		$workflow->method( 'getLandingPageTitle' )->willReturn( null );

		// Check that it doesn't intercept if the message is empty
		self::assertEquals( false, $workflow->shouldInterceptEditPage( $editPage ) );
	}

}
