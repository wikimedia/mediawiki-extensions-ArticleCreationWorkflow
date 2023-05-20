<?php

namespace ArticleCreationWorkflow\Tests;

use ArticleCreationWorkflow\Workflow;
use DerivativeContext;
use HashConfig;
use MediaWikiIntegrationTestCase;
use OutputPage;
use RequestContext;
use Title;
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
	 * @param Title|string $title
	 * @param bool $expected
	 */
	public function testShouldInterceptPage( array $allowedMap, $title, $expected ) {
		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )
			->will( self::returnValueMap( $allowedMap ) );
		if ( $title === 'existing' ) {
			$title = $this->createMock( Title::class );
			$title->method( 'exists' )
				->willReturn( true );
			$title->method( 'getContentModel' )
				->willReturn( CONTENT_MODEL_WIKITEXT );
		}
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
	 * @param Title|string $title
	 * @param bool $expected
	 */
	public function testInterceptIfNeeded( array $allowedMap, $title, $expected ) {
		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )
			->will( self::returnValueMap( $allowedMap ) );
		if ( $title === 'existing' ) {
			$title = $this->createMock( Title::class );
			$title->method( 'exists' )
				->willReturn( true );
			$title->method( 'getContentModel' )
				->willReturn( CONTENT_MODEL_WIKITEXT );
		}
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

		/** @var Workflow $workflow */
		self::assertEquals( $expected, $workflow->interceptIfNeeded( $title, $user, $context ) );
	}

	public static function providePageInterception() {
		$anonAllowMap = [
			[ 'autoconfirmed', false ],
			[ 'createpage', false ],
			[ 'createpagemainns', false ],
		];
		$newbieAllowList = [
			[ 'autoconfirmed', false ],
			[ 'createpage', true ],
			[ 'createpagemainns', false ],
		];
		$confirmedAllowList = [
			[ 'autoconfirmed', true ],
			[ 'createpage', true ],
			[ 'createpagemainns', true ],
		];

		$mainspacePage = Title::newFromText( 'Some nonexistent page' );
		$miscPage = Title::newFromText( 'Project:Nonexistent too' );
		$existingPage = 'existing';

		return [
			[ $anonAllowMap, $miscPage, false, 'Wrong NS, do nothing' ],
			[ $anonAllowMap, $existingPage, false, 'Page exists, do nothing' ],
			[ $anonAllowMap, $mainspacePage, false, 'Anon attempting to create a page, do nothing' ],
			[ $confirmedAllowList, $mainspacePage, false, 'Confirmed user in mainspace, do nothing' ],
			[ $confirmedAllowList, $existingPage, false, 'Confirmed user on an existing page, do nothing' ],
			[ $confirmedAllowList, $miscPage, false, 'Confirmed user not in mainspace, do nothing' ],
			[ $newbieAllowList, $mainspacePage, true, 'Newbie attempting to create a page, intercept' ],
			[ $newbieAllowList, $existingPage, false, 'Newbie on an existing page, do nothing' ],
			[ $newbieAllowList, $miscPage, false, 'Newbie attempting to create a non-mainspace page, do nothing' ],
		];
	}

	/**
	 * @covers \ArticleCreationWorkflow\Workflow::shouldInterceptPage()
	 */
	public function testLandingPageExistence() {
		$title = Title::newFromText( 'Test page' );
		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )
			->will( self::returnValue( true ) );
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
