<?php 

class ArticleCreationHooks {

	/**
	 * Redirect users to a page specified by returnto upon successful account creation
	 * @param $welcome_creation_msg - string
	 * @param $injected_html - html string
	 * @return bool
	 */
	public static function BeforeWelcomeCreation( &$welcome_creation_msg, &$injected_html ) {
		global $wgRequest, $wgOut;

		$title = Title::newFromText( $wgRequest->getVal( 'returnto' ) );
		if ( !$title instanceof Title ) {
			$title = Title::newMainPage();
		}
		$redirectUrl = $title->getFullURL( $wgRequest->getVal( 'returntoquery' ) );
		global $wgSecureLogin;
		if( $wgSecureLogin && !$wgRequest->getCheck( 'wpStickHTTPS' ) ) {
			$redirectUrl = preg_replace( '/^https:/', 'http:', $redirectUrl );
		}
		$wgOut->redirect( $redirectUrl );
		
		return true;
	}
	
	/**
	 * If the edit page is coming from red link, redirect users to article-non-existing page
	 * @param $editPage EditPage
	 * @return bool
	 */
	public static function AlternateEdit( $editPage ) {
		global $wgRequest, $wgOut;

		if ( ! ArticleCreationUtil::isEnabled() ) {
			return true;
		}

		$title = $editPage->mArticle->getTitle();

		if ( $wgRequest->getBool( 'redlink' ) ) {
			$wgOut->redirect( $title->getFullURL() );
		}

		return true;
	}

	/**
	 * Customized html that shows an article doesn't exist
	 * @param $article Article
	 * @return bool
	 */
	public static function BeforeDisplayNoArticleText( $article ) {
		global $wgOut;

		if ( ArticleCreationUtil::isEnabled() ) {
			$wgOut->addModules( array( 'ext.articleCreation.init' ) );
		}

		return true;
	}

	public static function getGlobalVariables( &$vars ) {
		global $wgArticleCreationConfig, $wgUser, $wgArticleCreationButtons, $wgTitle;

		if ( ! $wgTitle->isSpecial( 'ArticleCreationLanding' ) ) {
			return true;
		}

		$vars['acConfig'] = $wgArticleCreationConfig + 
			array(
				'enabled' => ArticleCreationUtil::isEnabled(),
				'tracking-turned-on' =>  ArticleCreationUtil::trackingEnabled(),
				'tracking-code-prefix' => ArticleCreationUtil::trackingCodePrefix(),
				'variant' => ArticleCreationTemplates::getLandingVariant( $wgTitle ),
				'acwbucket' => ArticleCreationUtil::trackingBucket(),
			);
	
		return true;
	}

	public static function configSearchTitle( &$vars ) {
		global $wgRequest;

		$vars['acSearch'] = $wgRequest->getVal( 'search' );

		return true;
	}

	/**
	 * Alter 'Create' Link behavior in search result page
	 * @param $title Title
	 * @param $params array
	 * @return bool
	 */
	public static function SpecialSearchCreateLink( $title, &$params ) {
		global $wgOut, $wgHooks;

		if ( ArticleCreationUtil::isEnabled() && $title->userCan( 'create' ) &&
			$title->userCan('edit') ) {
			$wgHooks['MakeGlobalVariablesScript'][] = 'ArticleCreationHooks::configSearchTitle';
			$wgOut->addModules( array( 'ext.articleCreation.searchResult' ) );
		}

		return true;
	}

	/**
	 * Pushes the tracking fields into the edit page
	 * @see http://www.mediawiki.org/wiki/Manual:Hooks/EditPage::showEditForm:fields
	 * @param $editPage EditPage
	 * @param $output OutputPage
	 * @return bool
	 */
	public static function pushTrackingFieldsToEdit( $editPage, $output ) {
		$res = self::validateTrackingBucketSource();
		if ( $res ) {
			$editPage->editFormTextAfterContent .= Html::hidden( 'acwbucket', $res['bucket'] );
			$editPage->editFormTextAfterContent .= Html::hidden( 'acwsource', $res['source'] );
		}

		return true;
	}

	/**
	 * Tracks successful save from article creation workflow
	 * @see http://www.mediawiki.org/wiki/Manual:Hooks/ArticleSaveComplete
	 * @param $article WikiPage
	 * @param $user
	 * @param $text
	 * @param $summary
	 * @param $minoredit
	 * @param $watchthis
	 * @param $sectionanchor
	 * @param $flags
	 * @param $revision
	 * @param $status
	 * @param $baseRevId
	 * @return bool
	 */
	public static function trackEditSuccess( &$article, &$user, $text,
			$summary, $minoredit, $watchthis, $sectionanchor, &$flags,
			$revision, &$status, $baseRevId /*, &$redirect */ ) { // $redirect not passed in 1.18wmf1

		$res = self::validateTrackingBucketSource();

		if ( $res ) {
			if ( $res['source'] ) {
				$res['source'] .= '_';
			}
			ArticleCreationUtil::clickTracking( $res['bucket'] . '-' . 'create_' . 
								$res['source'] . 'edit_success', $article->getTitle() );
		}
		
		return true;
	}

	/**
	 * Tracks save attempt from article creation workflow
	 * @see http://www.mediawiki.org/wiki/Manual:Hooks/EditPage::attemptSave
	 * @param $editpage EditPage
	 * @return bool
	 */
	public static function trackEditAttempt( $editpage ) {
		$res = self::validateTrackingBucketSource();

		if ( $res ) {
			if ( $res['source'] ) {
				$res['source'] .= '_';
			}
			ArticleCreationUtil::clickTracking( $res['bucket'] . '-' . 'create_' . $res['source'] . 
								'edit_attempt', $editpage->getArticle()->getTitle() );
		}
		
		return true;
	}
	
	/**
	 * Validate the tracking bucket and source
	 * @return array|bool
	 */
	private static function validateTrackingBucketSource() {
		global $wgRequest;
		$bucket  = $wgRequest->getVal( 'acwbucket' );
		$source  = $wgRequest->getVal( 'acwsource' );
		
		if ( in_array( $bucket, ArticleCreationUtil::getValidTrackingBucket(), true ) &&
			in_array( $source, ArticleCreationUtil::getValidTrackingSource(), true ) ) {
			return array( 'bucket' => $bucket, 'source' => $source );
		} else {
			return false;		
		}
	}
}
