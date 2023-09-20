<?php

namespace ArticleCreationWorkflow\Tests;

use ArticleCreationWorkflow\Workflow;
use DerivativeContext;
use HashConfig;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageCache;
use OutputPage;
use RequestContext;
use User;

/**
 * @group ArticleCreationWorkflow
 */
class WorkflowTest extends MediaWikiIntegrationTestCase {
	private const LANDING_PAGE_TITLE = 'this is a title, trust me';

	/**
	 * @dataProvider providePageInterception
	 *
	 * @covers \ArticleCreationWorkflow\Workflow::shouldInterceptPage()
	 *
	 * @param array $allowedMap
	 * @param int $namespace
	 * @param bool $exists
	 * @param bool $expected
	 */
	public function testShouldInterceptPage( array $allowedMap, int $namespace, bool $exists, $expected ) {
		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )
			->willReturnMap( $allowedMap );
		$title = Title::makeTitle( $namespace, 'TestShouldInterceptPage' );
		$title->resetArticleID( $exists ? 42 : 0 );
		$title->setContentModel( CONTENT_MODEL_WIKITEXT );
		$context = new DerivativeContext( RequestContext::getMain() );
		$context->setTitle( $title );
		$context->setUser( $user );

		$config = new HashConfig();

		$landingPage = $this->createMock( Title::class );
		$landingPage->method( 'exists' )->willReturn( true );
		$workflow = $this->getMockBuilder( Workflow::class )
			->onlyMethods( [ 'getLandingPageTitle' ] )
			->setConstructorArgs( [ $config ] )
			->getMock();
		$workflow->method( 'getLandingPageTitle' )->willReturn( $landingPage );

		/** @var Workflow $workflow */
		self::assertEquals( $expected, $workflow->shouldInterceptPage( $title, $user ) );
	}

	/**
	 * @dataProvider providePageInterception
	 *
	 * @covers \ArticleCreationWorkflow\Workflow::interceptIfNeeded()
	 *
	 * @param array $allowedMap
	 * @param int $namespace
	 * @param bool $exists
	 * @param bool $expected
	 */
	public function testInterceptIfNeeded( array $allowedMap, int $namespace, bool $exists, $expected ) {
		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )
			->willReturnMap( $allowedMap );
		$title = Title::makeTitle( $namespace, 'TestInterceptIfNeeded' );
		$title->resetArticleID( $exists ? 42 : 0 );
		$title->setContentModel( CONTENT_MODEL_WIKITEXT );
		$output = $this->createMock( OutputPage::class );

		if ( $expected ) {
			$output->expects( self::once() )
				->method( 'addHTML' );
		} else {
			$output->expects( self::never() )
				->method( 'addHTML' );
		}

		$context = new DerivativeContext( RequestContext::getMain() );
		$context->setTitle( $title );
		$context->setUser( $user );
		/** @var OutputPage $output */
		$context->setOutput( $output );

		$config = new HashConfig();

		$landingPage = $this->createMock( Title::class );
		$landingPage->method( 'exists' )->willReturn( true );
		$landingPage->method( 'getPrefixedText' )
			->willReturn( self::LANDING_PAGE_TITLE );
		$workflow = $this->getMockBuilder( Workflow::class )
			->onlyMethods( [ 'getLandingPageTitle' ] )
			->setConstructorArgs( [ $config ] )
			->getMock();
		$workflow->method( 'getLandingPageTitle' )->willReturn( $landingPage );
		$msgCache = $this->createMock( MessageCache::class );
		$msgCache->method( 'parse' )->willReturn( '' );
		$this->setService( 'MessageCache', $msgCache );

		/** @var Workflow $workflow */
		self::assertEquals( $expected, $workflow->interceptIfNeeded( $title, $user, $context ) );
	}

	public static function providePageInterception() {
		$anonAllowMap = [
			[ 'autoconfirmed', null, false ],
			[ 'createpage', null, false ],
			[ 'createpagemainns', null, false ],
		];
		$newbieAllowList = [
			[ 'autoconfirmed', null, false ],
			[ 'createpage', null, true ],
			[ 'createpagemainns', null, false ],
		];
		$confirmedAllowList = [
			[ 'autoconfirmed', null, true ],
			[ 'createpage', null, true ],
			[ 'createpagemainns', null, true ],
		];

		return [
			[ $anonAllowMap, NS_PROJECT, false, false, 'Wrong NS, do nothing' ],
			[ $anonAllowMap, NS_MAIN, true, false, 'Page exists, do nothing' ],
			[ $anonAllowMap, NS_MAIN, false, false, 'Anon attempting to create a page, do nothing' ],
			[ $confirmedAllowList, NS_MAIN, false, false, 'Confirmed user in mainspace, do nothing' ],
			[ $confirmedAllowList, NS_MAIN, true, false, 'Confirmed user on an existing page, do nothing' ],
			[ $confirmedAllowList, NS_PROJECT, false, false, 'Confirmed user not in mainspace, do nothing' ],
			[ $newbieAllowList, NS_MAIN, false, true, 'Newbie attempting to create a page, intercept' ],
			[ $newbieAllowList, NS_MAIN, true, false, 'Newbie on an existing page, do nothing' ],
			[
				$newbieAllowList,
				NS_PROJECT,
				false,
				false,
				'Newbie attempting to create a non-mainspace page, do nothing'
			],
		];
	}

	/**
	 * @covers \ArticleCreationWorkflow\Workflow::shouldInterceptPage()
	 */
	public function testLandingPageExistence() {
		$title = Title::makeTitle( NS_MAIN, 'TestLandingPageExistence' );
		$title->resetArticleID( 0 );
		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )
			->willReturn( true );
		$config = new HashConfig( [
			'ArticleCreationLandingPage' => 'Nonexistent page',
		] );

		$workflow = $this->getMockBuilder( Workflow::class )
			->onlyMethods( [ 'getLandingPageTitle' ] )
			->setConstructorArgs( [ $config ] )
			->getMock();
		$workflow->method( 'getLandingPageTitle' )->willReturn( null );

		// Check that it doesn't intercept if the message is empty
		/** @var Workflow $workflow */
		/** @var User $user */
		self::assertFalse( $workflow->shouldInterceptPage( $title, $user ) );
	}

}
