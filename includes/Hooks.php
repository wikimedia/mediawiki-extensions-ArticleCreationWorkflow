<?php

namespace ArticleCreationWorkflow;

use Article;
use MediaWiki\Actions\Hook\GetActionNameHook;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\BeforeDisplayNoArticleTextHook;
use MediaWiki\Permissions\Hook\TitleQuickPermissionsHook;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Hook handlers
 */
class Hooks implements
	GetActionNameHook,
	BeforeDisplayNoArticleTextHook,
	TitleQuickPermissionsHook
{
	/**
	 * TitleQuickPermissions hook handler
	 * Prohibits creating pages in main namespace for users without a special permission
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/TitleQuickPermissions
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param array &$errors
	 * @param bool $doExpensiveQueries
	 * @param bool $short
	 * @return bool
	 */
	public function onTitleQuickPermissions( $title,
		$user,
		$action,
		&$errors,
		$doExpensiveQueries,
		$short
	) {
		if ( $action === 'create'
			&& $title->inNamespace( NS_MAIN )
			&& !$user->isAllowed( 'createpagemainns' )
		) {
			$errors[] = !$user->isNamed() ? [ 'nocreatetext' ] : [ 'nocreate-loggedin' ];
			return false;
		}
		return true;
	}

	/**
	 * GetActionName hook handler
	 *
	 * @param IContextSource $context Request context
	 * @param string &$action Default action name, reassign to change it
	 * @return void This hook must not abort, it must return no value
	 */
	public function onGetActionName( IContextSource $context, string &$action ): void {
		if ( $action !== 'edit' ) {
			return;
		}
		$workflow = self::getWorkflow();
		$title = $context->getTitle();
		$user = $context->getUser();
		if ( $workflow->shouldInterceptPage( $title, $user ) ) {
			// The user wouldn't be allowed to edit anyway, so pretend we're in the 'view' action,
			// so that we can intercept it in onBeforeDisplayNoArticleText.
			$action = 'view';
		}
	}

	/**
	 * BeforeDisplayNoArticleText hook handler
	 *
	 * @param Article $article The (empty) article
	 * @return bool This hook can abort
	 */
	public function onBeforeDisplayNoArticleText( $article ) {
		$workflow = self::getWorkflow();
		$context = $article->getContext();
		$user = $context->getUser();
		$title = $article->getTitle();

		$wasIntercepted = $workflow->interceptIfNeeded( $title, $user, $context );

		// If we displayed our own message, abort the hook by returning `false`
		// to suppress the default message, otherwise let it continue.
		return !$wasIntercepted;
	}

	/**
	 * @return Workflow
	 */
	private static function getWorkflow() {
		static $cached;

		if ( !$cached ) {
			$config = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'ArticleCreationWorkflow' );
			$cached = new Workflow( $config );
		}

		return $cached;
	}
}
