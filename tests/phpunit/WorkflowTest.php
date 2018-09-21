<?php

namespace ArticleCreationWorkflow\Tests;

use ArticleCreationWorkflow\Workflow;
use DerivativeContext;
use HashConfig;
use MediaWikiTestCase;
use OutputPage;
use RequestContext;
use Title;
use User;

/**
 * @group ArticleCreationWorkflow
 */
class WorkflowTest extends MediaWikiTestCase {
	const REDIRECT_URL = 'this is a URL, trust me';

	/**
	 * @dataProvider providePageInterception
	 *
	 * @covers \ArticleCreationWorkflow\Workflow::shouldInterceptPage()
	 *
	 * @param User $user
	 * @param Title $title
	 * @param bool $expected
	 */
	public function testShouldInterceptPage( User $user, Title $title, $expected ) {
		$context = new DerivativeContext( RequestContext::getMain() );
		$context->setTitle( $title );
		$context->setUser( $user );

		$config = new HashConfig();

		$landingPage = $this->getMock( Title::class, [ 'exists' ] );
		$landingPage->method( 'exists' )->willReturn( true );
		$workflow = $this->getMock( Workflow::class, [ 'getLandingPageTitle' ], [ $config ] );
		$workflow->method( 'getLandingPageTitle' )->willReturn( $landingPage );

		/** @var Workflow $workflow */
		self::assertEquals( $expected, $workflow->shouldInterceptPage( $title, $user ) );
	}

	/**
	 * @dataProvider providePageInterception
	 *
	 * @covers \ArticleCreationWorkflow\Workflow::interceptIfNeeded()
	 *
	 * @param User $user
	 * @param Title $title
	 * @param bool $expected
	 */
	public function testInterceptIfNeeded( User $user, Title $title, $expected ) {
		$output = $this->getMockBuilder( OutputPage::class )
			->disableOriginalConstructor()
			->setMethods( [ 'redirect' ] )
			->getMock();

		if ( $expected ) {
			$output->expects( self::once() )
				->method( 'redirect' )
				->with( self::REDIRECT_URL );
		} else {
			$output->expects( self::never() )
				->method( 'redirect' );
		}

		$context = new DerivativeContext( RequestContext::getMain() );
		$context->setTitle( $title );
		$context->setUser( $user );
		/** @var OutputPage $output */
		$context->setOutput( $output );

		$config = new HashConfig();

		$landingPage = $this->getMock( Title::class, [ 'exists', 'getFullURL' ] );
		$landingPage->method( 'exists' )->willReturn( true );
		$landingPage->method( 'getFullURL' )
			->with( [ 'page' => $title->getPrefixedText() ] )
			->willReturn( self::REDIRECT_URL );
		$workflow = $this->getMock( Workflow::class, [ 'getLandingPageTitle' ], [ $config ] );
		$workflow->method( 'getLandingPageTitle' )->willReturn( $landingPage );

		/** @var Workflow $workflow */
		self::assertEquals( $expected, $workflow->interceptIfNeeded( $title, $user, $context ) );
	}

	public function providePageInterception() {
		$anon = $this->getMock( 'User' );
		$anonmap = [
			[ 'autoconfirmed', false ],
			[ 'createpage', false ],
			[ 'createpagemainns', false ],
		];
		$anon->method( 'isAllowed' )
			->will( self::returnValueMap( $anonmap ) );

		$newbie = $this->getMock( 'User' );
		$newbiemap = [
			[ 'autoconfirmed', false ],
			[ 'createpage', true ],
			[ 'createpagemainns', false ],
		];
		$newbie->method( 'isAllowed' )
			->will( self::returnValueMap( $newbiemap ) );

		$confirmed = $this->getMock( 'User' );
		$confirmedmap = [
			[ 'autoconfirmed', true ],
			[ 'createpage', true ],
			[ 'createpagemainns', true ],
		];
		$confirmed->method( 'isAllowed' )
			->will( self::returnValueMap( $confirmedmap ) );

		$mainspacePage = Title::newFromText( 'Some nonexistent page' );
		$miscPage = Title::newFromText( 'Project:Nonexistent too' );
		$existingPage = $this->getMock( 'Title' );
		$existingPage->method( 'exists' )
			->willReturn( true );
		$existingPage->method( 'getContentModel' )
			->willReturn( CONTENT_MODEL_WIKITEXT );

		return [
			[ $anon, $miscPage, false, 'Wrong NS, do nothing' ],
			[ $anon, $existingPage, false, 'Page exists, do nothing' ],
			[ $anon, $mainspacePage, false, 'Anon attempting to create a page, do nothing' ],
			[ $confirmed, $mainspacePage, false, 'Confirmed user in mainspace, do nothing' ],
			[ $confirmed, $existingPage, false, 'Confirmed user on an existing page, do nothing' ],
			[ $confirmed, $miscPage, false, 'Confirmed user not in mainspace, do nothing' ],
			[ $newbie, $mainspacePage, true, 'Newbie attempting to create a page, intercept' ],
			[ $newbie, $existingPage, false, 'Newbie on an existing page, do nothing' ],
			[ $newbie, $miscPage, false, 'Newbie attempting to create a non-mainspace page, do nothing' ],
		];
	}

	/**
	 * @covers \ArticleCreationWorkflow\Workflow::shouldInterceptPage()
	 */
	public function testLandingPageExistence() {
		$title = Title::newFromText( 'Test page' );
		$user = $this->getMock( 'User' );
		$user->method( 'isAllowed' )
			->will( self::returnValue( true ) );
		$config = new HashConfig( [
			'ArticleCreationLandingPage' => 'Nonexistent page',
		] );

		$workflow = $this->getMock( Workflow::class, [ 'getLandingPageTitle' ], [ $config ] );
		$workflow->method( 'getLandingPageTitle' )->willReturn( null );

		// Check that it doesn't intercept if the message is empty
		/** @var Workflow $workflow */
		/** @var User $user */
		self::assertEquals( false, $workflow->shouldInterceptPage( $title, $user ) );
	}

}
