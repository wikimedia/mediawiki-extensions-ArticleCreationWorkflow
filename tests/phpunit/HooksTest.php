<?php

namespace ArticleCreationWorkflow\Tests;

use ArticleCreationWorkflow\Hooks;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;

/**
 * @group ArticleCreationWorkflow
 */
class HooksTest extends MediaWikiIntegrationTestCase {
	/**
	 * @dataProvider provideOnTitleQuickPermissions
	 *
	 * @covers \ArticleCreationWorkflow\Hooks::onTitleQuickPermissions()
	 *
	 * @param bool $mockNamedAccount
	 * @param bool $mockCanCreate
	 * @param Title $title
	 * @param string $action
	 * @param array $expected
	 */
	public function testOnTitleQuickPermissions( $mockNamedAccount, $mockCanCreate, Title $title, $action, $expected ) {
		$user = $this->makeUser( $mockNamedAccount, $mockCanCreate );
		$errors = [];
		$this->callOnTitleQuickPermissions( $user, $title, $action, $expected, $errors );
		self::assertEquals( $expected, $errors );

		$errors = [ [ 'do not touch this' ] ];
		$this->callOnTitleQuickPermissions( $user, $title, $action, $expected, $errors );
		self::assertEquals( array_merge( [ [ 'do not touch this' ] ], $expected ), $errors );
	}

	private function callOnTitleQuickPermissions( User $user,
		Title $title,
		$action,
		$expected,
		array &$errors
	) {
		$ret = ( new Hooks )->onTitleQuickPermissions( $title, $user, $action, $errors, false, false );
		self::assertEquals( !$expected, $ret,
			'onTitleQuickPermissions() should return false on permission errors, true otherwise'
		);
	}

	public static function provideOnTitleQuickPermissions() {
		$mainspace = Title::newFromText( 'Mainspace page' );
		$nonMainspace = Title::newFromText( 'MediaWiki:Non-mainspace page' );

		return [
			// named account
			[ true, false, $mainspace, 'read', [] ],
			[ true, false, $nonMainspace, 'read', [] ],
			[ true, false, $mainspace, 'create', [ [ 'nocreate-loggedin' ] ] ],
			[ true, false, $nonMainspace, 'create', [] ],

			// named account & AcwDisabled
			[ true, true, $mainspace, 'read', [] ],
			[ true, true, $nonMainspace, 'read', [] ],
			[ true, true, $mainspace, 'create', [] ],
			[ true, true, $nonMainspace, 'create', [] ],

			// newbie
			[ false, false, $mainspace, 'read', [] ],
			[ false, false, $nonMainspace, 'read', [] ],
			[ false, false, $mainspace, 'create', [ [ 'nocreatetext' ] ] ],
			[ false, false, $nonMainspace, 'create', [] ],

			// autoconfirmed
			[ false, true, $mainspace, 'read', [] ],
			[ false, true, $nonMainspace, 'read', [] ],
			[ false, true, $mainspace, 'create', [] ],
			[ false, true, $nonMainspace, 'create', [] ],
		];
	}

	private function makeUser( $isNamed, $canCreate ) {
		$user = $this->createMock( User::class );

		$user->method( 'isNamed' )
			->willReturn( $isNamed );

		$user->method( 'isAllowed' )
			->with( 'createpagemainns' )
			->willReturn( $canCreate );

		return $user;
	}
}
