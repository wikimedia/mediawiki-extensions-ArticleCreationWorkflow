<?php

namespace ArticleCreationWorkflow\Tests;

use ArticleCreationWorkflow\Hooks;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use User;

/**
 * @group ArticleCreationWorkflow
 */
class HooksTest extends MediaWikiIntegrationTestCase {
	/**
	 * @dataProvider provideOnTitleQuickPermissions
	 *
	 * @covers \ArticleCreationWorkflow\Hooks::onTitleQuickPermissions()
	 *
	 * @param bool $mockAnon
	 * @param bool $mockCanCreate
	 * @param Title $title
	 * @param string $action
	 * @param array $expected
	 */
	public function testOnTitleQuickPermissions( $mockAnon, $mockCanCreate, Title $title, $action, $expected ) {
		$user = $this->makeUser( $mockAnon, $mockCanCreate );
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
		$ret = Hooks::onTitleQuickPermissions( $title, $user, $action, $errors );
		self::assertEquals( !$expected, $ret,
			'onTitleQuickPermissions() should return false on permission errors, true otherwise'
		);
	}

	public static function provideOnTitleQuickPermissions() {
		$mainspace = Title::newFromText( 'Mainspace page' );
		$nonMainspace = Title::newFromText( 'MediaWiki:Non-mainspace page' );

		return [
			// anon
			[ true, false, $mainspace, 'read', [] ],
			[ true, false, $nonMainspace, 'read', [] ],
			[ true, false, $mainspace, 'create', [ [ 'nocreatetext' ] ] ],
			[ true, false, $nonMainspace, 'create', [] ],

			// anon AcwDisabled
			[ true, true, $mainspace, 'read', [] ],
			[ true, true, $nonMainspace, 'read', [] ],
			[ true, true, $mainspace, 'create', [] ],
			[ true, true, $nonMainspace, 'create', [] ],

			// newbie
			[ false, false, $mainspace, 'read', [] ],
			[ false, false, $nonMainspace, 'read', [] ],
			[ false, false, $mainspace, 'create', [ [ 'nocreate-loggedin' ] ] ],
			[ false, false, $nonMainspace, 'create', [] ],

			// autoconfirmed
			[ false, true, $mainspace, 'read', [] ],
			[ false, true, $nonMainspace, 'read', [] ],
			[ false, true, $mainspace, 'create', [] ],
			[ false, true, $nonMainspace, 'create', [] ],
		];
	}

	private function makeUser( $anon, $canCreate ) {
		$user = $this->createMock( User::class );

		$user->method( 'isAnon' )
			->willReturn( $anon );

		$user->method( 'isAllowed' )
			->with( 'createpagemainns' )
			->willReturn( $canCreate );

		return $user;
	}
}
